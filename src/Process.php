<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Exceptions\CommandExecutionException;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Promise\DeferredFactory;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\Deferred;

/**
 * Class encapsulating the lifetime of a process.
 */
final class Process implements ProcessInterface
{
    /**
     * @var EnvironmentInterface The environment to execute the command in.
     */
    private $environment;

    /**
     * @var DeferredFactory Factory that builds deferred instances.
     */
    private $deferredFactory;

    /**
     * @var string The command executed to create this process.
     */
    private $command;

    /**
     * @var ProcessObserverInterface Observer watching process state.
     */
    private $observer;

    /**
     * @var int (microseconds) How long to wait for a command to finish executing, -1 to wait indefinitely.
     */
    private $timeout;

    /**
     * @var int (microseconds) The amount of time to sleep for when polling for completion, defaults to 1/100 of a
     *  second.
     */
    private $pollTimeout;

    /**
     * @var float Unix timestamp with microseconds.
     */
    private $startTime;

    /**
     * @var int The exit code of the process, set once the process has exited.
     */
    private $exitCode;

    /**
     * @var string Captured data from stdout.
     */
    private $fullStdOut = '';

    /**
     * @var string Captured data from stderr.
     */
    private $fullStdErr = '';

    /**
     * @var array Pipes populated by proc_open.
     */
    private $pipeList = [];

    /**
     * @var resource Process resource returned by proc_open.
     */
    private $process = null;

    /**
     * @var Process ID of the running process.
     */
    private $pid;

    /**
     * @var ProcessOutputInterface|null The result of running this process, or null.
     */
    private $output = null;


    /**
     * Constructor.
     *
     * @throws CommandExecutionException
     *
     * @param EnvironmentInterface $environment
     * @param DeferredFactory $deferredFactory
     * @param string $command
     * @param string $cwdOverride
     * @param int $timeout
     * @param int $pollTimeout
     * @param ProcessObserverInterface|null $observer
     */
    public function __construct(
        EnvironmentInterface $environment,
        DeferredFactory $deferredFactory,
        $command,
        $cwdOverride,
        $timeout = -1,
        $pollTimeout = 1000,
        ProcessObserverInterface $observer = null
    ) {
        $this->environment = $environment;
        $this->deferredFactory = $deferredFactory;
        $this->command = $command;
        $this->observer = $observer;
        if (is_null($this->observer)) {
            $this->observer = new NullProcessObserver();
        }

        // Store CWD, set to override
        $prevCwd = getcwd();
        chdir($cwdOverride);

        $this->process = proc_open(
            $command,
            [
                self::STDOUT => ['pipe', 'w'],
                self::STDERR => ['pipe', 'w']
            ],
            $this->pipeList
        );

        if (!is_resource($this->process)) {
            throw new CommandExecutionException('Call to proc_open failed for unknown reason.');
        }

        $this->pid = $this->getStatus()['pid'];
        $this->startTime = microtime(true);

        // Mark pipe streams as non-blocking
        foreach ($this->pipeList as $pipe) {
            stream_set_blocking($pipe, 0);
        }

        // Notify observer of process creation.
        $this->observer->processCreated($this->pid, $command);

        // Reset CWD to previous
        chdir($prevCwd);

        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
    }

    /**
     * {@inheritDoc}
     */
    public function isRunning()
    {
        $status = $this->getStatus();

        $this->observer->processPolled($this->pid, round(microtime(true) - $this->startTime) * 1000);

        return $status['running'];
    }

    /**
     * {@inheritDoc}
     */
    public function wait(\Closure $callback = null)
    {
        while ($this->isRunning()) {
            if ($this->hasExceededTimeout()) {
                $this->stop();
            }

            $this->readStreams($callback);
            usleep($this->pollTimeout);
        }

        // Mark pipe streams as non-blocking
        foreach ($this->pipeList as $pipe) {
            stream_set_blocking($pipe, 1);
        }

        $this->readStreams($callback);

        return $this->getProcessOutput();
    }

    /**
     * {@inheritDoc}
     */
    public function stop($timeout = 1000000)
    {
        $originalTime = microtime(true);
        $this->sendSignal(ProcessInterface::SIGTERM);

        while ($this->isRunning()) {
            $time = microtime(true);

            // If term hasn't succeeded by the specified timeout then try and kill
            if (($time - $originalTime) * 1000000 > $timeout) {
                $this->sendSignal(ProcessInterface::SIGKILL);
                break;
            }

            usleep($this->pollTimeout);
        }

        return $this->getProcessOutput();
    }

    /**
     * @inheritDoc
     */
    public function sendSignal($signal)
    {
        $this->observer->sentSignal($this->pid, $signal);

        $this->environment->sendSignal($this->process, $signal);
    }

    /**
     * @inheritDoc
     */
    public function readOutput($streamId)
    {
        $data = stream_get_contents($this->pipeList[$streamId]);
        return $data;
    }

    /**
     * @inheritDoc
     */
    private function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * @inheritDoc
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @inheritDoc
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @inheritDoc
     */
    public function getPromise(LoopInterface $eventLoop)
    {
        $deferred = $this->deferredFactory->buildDeferred();

        // Poll the process for completion
        $eventLoop->addPeriodicTimer(
            0.1,
            function (TimerInterface $timer) use ($eventLoop, $deferred) {
                $this->readStreams();

                // Process has terminated
                if (!$this->isRunning()) {
                    $eventLoop->cancelTimer($timer);
                    $output = $this->getProcessOutput();

                    // Resolve or reject promise
                    if (0 === $output->getExitCode()) {
                        $deferred->resolve($output);
                    } else {
                        $deferred->reject($output);
                    }
                }
            }
        );


        return $deferred->promise();
    }

    /**
     * Returns true if the process has been running for longer than the specified timeout.
     *
     * @return bool
     */
    private function hasExceededTimeout()
    {
        return -1 !== $this->timeout && (microtime(true) - $this->startTime) * 1000000 > $this->timeout;
    }

    /**
     * Proxy method to proc_get_status.
     *
     * This is used so that we always update the exit code when retrieving process status. This works around the issue
     * where only the last call after process termination contains the real exit code.
     *
     * See http://stackoverflow.com/a/7841550 For more information.
     *
     * @return array
     */
    private function getStatus()
    {
        $status = proc_get_status($this->process);

        if (!$status['running'] && is_null($this->exitCode)) {
            $this->exitCode = $status['exitcode'];
        }

        return $status;
    }

    /**
     * Read from the stdout & stderr streams, passing data to callback if provided.
     *
     * @param \Closure|null $callback
     */
    private function readStreams(\Closure $callback = null)
    {
        $stdOut = $this->readOutput(self::STDOUT);
        $stdErr = $this->readOutput(self::STDERR);

        $this->fullStdOut .= $stdOut;
        $this->fullStdErr .= $stdErr;

        if (!is_null($callback)) {
            $callback($stdOut, $stdErr);
        }

        $this->observer->stdOutRead($this->pid, $stdOut);
        $this->observer->stdErrRead($this->pid, $stdErr);
    }

    /**
     * Returns the output of the process (exit code, stdout & stderr).
     *
     * @return ProcessOutputInterface
     */
    private function getProcessOutput()
    {
        if (is_null($this->output)) {
            $this->output = new ProcessOutput($this->getExitCode(), $this->fullStdOut, $this->fullStdErr);
            $this->observer->processExited($this->pid, $this->output);
        }

        return $this->output;
    }
}
