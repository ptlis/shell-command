<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Exceptions\CommandExecutionException;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessInterface;

/**
 * Class encapsulating the lifetime of a process.
 */
class Process implements ProcessInterface
{
    /**
     * @var EnvironmentInterface The environment to execute the command in.
     */
    private $environment;

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
     * @var array Pipes populated by proc_open.
     */
    private $pipeList = [];

    /**
     * @var resource Process resource returned by proc_open.
     */
    private $process = null;


    /**
     * Constructor.
     *
     * @throws CommandExecutionException
     *
     * @param EnvironmentInterface $environment
     * @param string $command
     * @param string $cwdOverride
     * @param int $timeout
     * @param int $pollTimeout
     * @param ProcessObserverInterface|null $observer
     */
    public function __construct(
        EnvironmentInterface $environment,
        $command,
        $cwdOverride,
        $timeout = -1,
        $pollTimeout = 1000,
        ProcessObserverInterface $observer = null
    ) {
        $this->environment = $environment;
        $this->command = $command;
        $this->observer = $observer;

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
        $this->startTime = microtime(true);

        // Mark pipe streams as non-blocking
        foreach ($this->pipeList as $pipe) {
            stream_set_blocking($pipe, 0);
        }

        // Notify observer of process creation.
        if (!is_null($observer)) {
            $observer->processCreated($command);
        }

        // Reset CWD to previous
        chdir($prevCwd);

        if (!is_resource($this->process)) {
            throw new CommandExecutionException('Call to proc_open failed for unknown reason.');
        }

        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
    }

    /**
     * {@inheritDoc}
     */
    public function isRunning()
    {
        $status = $this->getStatus();

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
    }

    /**
     * @inheritDoc
     */
    public function sendSignal($signal)
    {
        if (!is_null($this->observer)) {
            $this->observer->sentSignal($signal);
        }

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
    public function getExitCode()
    {
        if ($this->isRunning()) {
            throw new \RuntimeException('Cannot get exit code of still-running process.');
        }

        return $this->exitCode;
    }

    /**
     * @inheritDoc
     */
    public function getPid()
    {
        $status = $this->getStatus();
        if (!$status['running']) {
            throw new \RuntimeException('Cannot get the process id of a process that has already exited.');
        }

        return $status['pid'];
    }

    /**
     * @inheritDoc
     */
    public function getCommand()
    {
        return $this->command;
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

            if (!is_null($this->observer)) {
                $this->observer->processExited($this->exitCode);
            }
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

        if (!is_null($callback)) {
            $callback($stdOut, $stdErr);
        }

        if (!is_null($this->observer)) {
            $this->observer->stdOutRead($stdOut);
            $this->observer->stdErrRead($stdErr);
        }
    }
}
