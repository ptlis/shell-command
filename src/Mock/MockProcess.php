<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;
use ptlis\ShellCommand\ProcessOutput;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use RuntimeException;

/**
 * Mock running process. Waits for the specified time before completing.
 */
class MockProcess implements ProcessInterface
{
    private string $command;
    private int $runFor;
    private ProcessOutputInterface $result;
    private int $pid;
    private bool $stopped = false;
    private float $startTime;
    /** @var array<int, array<string>> */
    private array $inputs = [];

    public function __construct(
        string $command,
        ProcessOutputInterface $result,
        int $runFor = 1000,
        int $pid = 123
    ) {
        $this->command = $command;
        $this->runFor = $runFor;
        $this->result = $result;
        $this->pid = $pid;
        $this->startTime = \microtime(true);
    }

    public function isRunning(): bool
    {
        return (
            !$this->stopped
            && ((\microtime(true) - $this->startTime) < ($this->runFor / 1000))
        );
    }

    public function wait(\Closure $callback = null): ProcessOutputInterface
    {
        while ($this->isRunning()) {
            \usleep(50);
        }

        if ($callback) {
            $callback($this->result->getStdOut(), $this->result->getStdErr());
        }

        return $this->result;
    }

    public function stop(int $timeout = 1000000): ProcessOutputInterface
    {
        $this->stopped = true;

        return $this->result;
    }

    public function readOutput(int $streamId): string
    {
        return match ($streamId) {
            ProcessInterface::STDOUT => $this->result->getStdOut(),
            ProcessInterface::STDERR => $this->result->getStdErr(),
            default => '',
        };
    }

    public function writeInput(string $input, int $streamId = ProcessInterface::STDIN, bool $appendNewline = true): void
    {
        $this->inputs[$streamId][] = $input . ($appendNewline ? "\n" : '');
    }

    public function sendSignal(string $signal): void
    {
        // Can only be one of SIGTERM or SIGKILL, force process to stop
        $this->stopped = true;
    }

    public function getPid(): int
    {
        if (!$this->isRunning()) {
            throw new RuntimeException('Stopped processed do not have a pid');
        }

        return $this->pid;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getPromise(LoopInterface $eventLoop): PromiseInterface
    {
        $deferred = new Deferred();

        // Poll the process for completion
        $eventLoop->addPeriodicTimer(
            0.1,
            function (TimerInterface $timer) use ($eventLoop, $deferred, &$fullStdOut, &$fullStdErr) {
                $fullStdOut .= $this->readOutput(ProcessInterface::STDOUT);
                $fullStdErr .= $this->readOutput(ProcessInterface::STDERR);

                // Process has terminated
                if (!$this->isRunning()) {
                    $eventLoop->cancelTimer($timer);
                    $output = new ProcessOutput(
                        $this->result->getExitCode(),
                        $fullStdOut,
                        $fullStdErr,
                        $this->command,
                        '.'
                    );

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
     * @return array<int, array<string>>
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }
}
