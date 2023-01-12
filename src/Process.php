<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

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
use React\Promise\PromiseInterface;

/**
 * Class encapsulating the lifetime of a process.
 */
final class Process implements ProcessInterface
{
    use TriggerDeprecationTrait;

    private readonly ProcessObserverInterface $observer;
    private readonly float $startTime;
    private ?int $exitCode = null;
    private string $fullStdOut = '';
    private string $fullStdErr = '';
    /** @var array<resource> */
    private array $pipeList = [];
    /** @var resource */
    private $process;
    public readonly int $pid;
    public readonly string $command;
    private ?ProcessOutputInterface $output = null;

    /**
     * @param array<string, string> $envVarList
     */
    public function __construct(
        private readonly EnvironmentInterface $environment,
        string $command,
        private readonly string $cwdOverride,
        private readonly array $envVarList = [],
        private readonly int $timeout = -1,
        private readonly int $pollTimeout = 1000,
        ProcessObserverInterface $observer = null
    ) {
        $this->observer = $observer ?? new NullProcessObserver();

        // Attempt to launch process
        $process = proc_open(
            $command,
            [
                self::STDIN  => ['pipe', 'r'],
                self::STDOUT => ['pipe', 'w'],
                self::STDERR => ['pipe', 'w']
            ],
            $this->pipeList,
            $cwdOverride,
            $this->getMergedEnvVars()
        );
        if (!\is_resource($process)) {
            throw new CommandExecutionException('Call to proc_open failed for unknown reason.');
        }

        $this->process = $process;
        $status = $this->getStatus();
        $this->pid = $status['pid'];
        $this->command = $status['command'];
        $this->startTime = \microtime(true);

        // Mark pipe streams as non-blocking
        foreach ($this->pipeList as $pipe) {
            \stream_set_blocking($pipe, false);
        }

        // Notify observer of process creation.
        $this->observer->processCreated($this->pid, $command, $cwdOverride, $envVarList);
    }

    public function isRunning(): bool
    {
        $status = $this->getStatus();
        $this->observer->processPolled(
            $this->pid,
            (int)\floor((\microtime(true) - $this->startTime) * 1000)
        );
        return $status['running'];
    }

    public function wait(\Closure $callback = null): ProcessOutputInterface
    {
        while ($this->isRunning()) {
            if ($this->hasExceededTimeout()) {
                $this->stop();
            }

            $this->readStreams($callback);
            \usleep($this->pollTimeout);
        }

        // Mark pipe streams as blocking
        foreach ($this->pipeList as $pipe) {
            \stream_set_blocking($pipe, true);
        }

        $this->readStreams($callback);

        return $this->getProcessOutput();
    }

    public function stop(int $timeout = 1000000): ProcessOutputInterface
    {
        $originalTime = \microtime(true);
        $this->sendSignal(ProcessInterface::SIGTERM);

        while ($this->isRunning()) {
            $time = \microtime(true);

            // If term hasn't succeeded by the specified timeout then try and kill
            if (($time - $originalTime) * 1000000 > $timeout) {
                $this->sendSignal(ProcessInterface::SIGKILL);
            }

            \usleep($this->pollTimeout);
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
        return (string)\stream_get_contents($this->pipeList[$streamId]);
    }

    public function writeInput(string $input, int $streamId = ProcessInterface::STDIN, bool $appendNewline = true): void
    {
        \fwrite($this->pipeList[$streamId], $input);
        if ($appendNewline) {
            \fwrite($this->pipeList[$streamId], "\n");
        }
    }

    /**
     * @deprecated Replaced with direct access to the pid property.
     */
    public function getPid(): int
    {
        $this->triggerDeprecationWarning(__METHOD__, 'pid');
        return $this->pid;
    }

    /**
     * @deprecated Replaced with direct access to the command property.
     */
    public function getCommand(): string
    {
        $this->triggerDeprecationWarning(__METHOD__, 'command');
        return $this->command;
    }

    public function getPromise(LoopInterface $eventLoop): PromiseInterface
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
        return -1 !== $this->timeout && (\microtime(true) - $this->startTime) * 1000000 > $this->timeout;
    }

    /**
     * Proxy method to proc_get_status.
     *
     * This is used so that we always update the exit code when retrieving process status. This works around the issue
     * where only the last call after process termination contains the real exit code.
     *
     * See http://stackoverflow.com/a/7841550 For more information.
     *
     * @return array{
     *     command: string,
     *     pid: int,
     *     running: bool,
     *     signaled: bool,
     *     stopped: bool,
     *     exitcode: int,
     *     termsig: int,
     *     stopsig: int
     * }
     */
    private function getStatus(): array
    {
        $status = \proc_get_status($this->process);

        if (!$status['running'] && \is_null($this->exitCode)) {
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

        if (!\is_null($callback)) {
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
        if (\is_null($this->output)) {
            $envVarString = '';
            foreach ($this->envVarList as $key => $value) {
                $envVarString .= $key . '=' . $this->environment->escapeShellArg($value) . ' ';
            }

            $this->output = new ProcessOutput(
                (int)$this->exitCode,
                $this->fullStdOut,
                $this->fullStdErr,
                $envVarString . $this->command,
                $this->cwdOverride
            );
            $this->observer->processExited($this->pid, $this->output);
        }

        return $this->output;
    }

    /**
     * Returns the PHP process's env vars merged with those passed to process
     *
     * @return array<string, string>
     */
    private function getMergedEnvVars(): array
    {
        return \array_merge(\getenv(), $this->envVarList);
    }
}
