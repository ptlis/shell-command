<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
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
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\Deferred;
use React\Promise\Promise;

/**
 * Class encapsulating the lifetime of a process.
 */
final class Process implements ProcessInterface
{
    /** @var EnvironmentInterface */
    private $environment;

    /** @var string */
    private $command;

    /** @var ProcessObserverInterface */
    private $observer;

    /** @var int */
    private $timeout;

    /** @var int */
    private $pollTimeout;

    /** @var float */
    private $startTime;

    /** @var int */
    private $exitCode;

    /** @var string */
    private $fullStdOut = '';

    /** @var string */
    private $fullStdErr = '';

    /** @var array */
    private $pipeList = [];

    /** @var resource */
    private $process = null;

    /** @var int */
    private $pid;

    /** @var ProcessOutputInterface|null*/
    private $output = null;


    public function __construct(
        EnvironmentInterface $environment,
        string $command,
        string $cwdOverride,
        array $envVarList = [],
        int $timeout = -1,
        int $pollTimeout = 1000,
        ProcessObserverInterface $observer = null
    ) {
        $this->environment = $environment;
        $this->command = $command;
        $this->observer = $observer;
        if (is_null($this->observer)) {
            $this->observer = new NullProcessObserver();
        }

        $this->process = proc_open(
            $command,
            [
                self::STDIN  => ['pipe', 'r'],
                self::STDOUT => ['pipe', 'w'],
                self::STDERR => ['pipe', 'w']
            ],
            $this->pipeList,
            $cwdOverride,
            array_merge(getenv(), $envVarList)  // Merge PHP process's env vars with those passed to process
        );

        if (!is_resource($this->process)) {
            throw new CommandExecutionException('Call to proc_open failed for unknown reason.');
        }

        $this->pid = $this->getStatus()['pid'];

        $this->startTime = microtime(true);

        // Mark pipe streams as non-blocking
        foreach ($this->pipeList as $pipe) {
            stream_set_blocking($pipe, false);
        }

        // Notify observer of process creation.
        $this->observer->processCreated($this->pid, $command, $cwdOverride, $envVarList);

        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
    }

    public function isRunning(): bool
    {
        $status = $this->getStatus();

        $this->observer->processPolled($this->pid, (int)floor((microtime(true) - $this->startTime) * 1000));

        return $status['running'];
    }

    public function wait(\Closure $callback = null): ProcessOutputInterface
    {
        while ($this->isRunning()) {
            if ($this->hasExceededTimeout()) {
                $this->stop();
            }

            $this->readStreams($callback);
            usleep($this->pollTimeout);
        }

        // Mark pipe streams as blocking
        foreach ($this->pipeList as $pipe) {
            stream_set_blocking($pipe, true);
        }

        $this->readStreams($callback);

        return $this->getProcessOutput();
    }

    public function stop(int $timeout = 1000000): ProcessOutputInterface
    {
        $originalTime = microtime(true);
        $this->sendSignal(ProcessInterface::SIGTERM);

        while ($this->isRunning()) {
            $time = microtime(true);

            // If term hasn't succeeded by the specified timeout then try and kill
            if (($time - $originalTime) * 1000000 > $timeout) {
                $this->sendSignal(ProcessInterface::SIGKILL);
            }

            usleep($this->pollTimeout);
        }

        return $this->getProcessOutput();
    }

    public function sendSignal(string $signal): void
    {
        $this->observer->sentSignal($this->pid, $signal);

        $this->environment->sendSignal($this->process, $signal);
    }

    public function readOutput(int $streamId): string
    {
        $data = stream_get_contents($this->pipeList[$streamId]);
        return $data;
    }

    public function writeInput(string $input, int $streamId = ProcessInterface::STDIN): void
    {
        fwrite($this->pipeList[$streamId], $input);
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getPromise(LoopInterface $eventLoop): Promise
    {
        $deferred = new Deferred();

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
     */
    private function hasExceededTimeout(): bool
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
     */
    private function getStatus(): array
    {
        $status = proc_get_status($this->process);

        if (!$status['running'] && is_null($this->exitCode)) {
            $this->exitCode = $status['exitcode'];
        }

        return $status;
    }

    /**
     * Read from the stdout & stderr streams, passing data to callback if provided.
     */
    private function readStreams(\Closure $callback = null): void
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
     */
    private function getProcessOutput(): ProcessOutputInterface
    {
        if (is_null($this->output)) {
            $this->output = new ProcessOutput($this->exitCode, $this->fullStdOut, $this->fullStdErr);
            $this->observer->processExited($this->pid, $this->output);
        }

        return $this->output;
    }
}
