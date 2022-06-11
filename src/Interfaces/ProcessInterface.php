<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Interfaces;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use RuntimeException;

/**
 * Interface defining a class to monitor process lifetimes.
 */
interface ProcessInterface
{
    /** Signal used to request that the process terminates. */
    public const SIGTERM = 'SIGTERM';
    /** Signal used to force the process to terminate. */
    public const SIGKILL = 'SIGKILL';

    public const STDIN = 0;
    public const STDOUT = 1;
    public const STDERR = 2;

    /**
     * Returns true if the process is still running.
     */
    public function isRunning(): bool;

    /**
     * Blocks execution until the command has finished executing.
     *
     * @param \Closure|null $callback Execute when the wait time has elapsed, is provided the latest contents of stdout
     *  and stderr.
     */
    public function wait(\Closure $callback = null): ProcessOutputInterface;

    /**
     * Stops the running process.
     *
     * @param int $timeout Maximum time (in microseconds) to wait before forcing the process to quit with SIGKILL.
     */
    public function stop(int $timeout = 1000000): ProcessOutputInterface;

    /**
     * Read the pending output from the specified stream.
     */
    public function readOutput(int $streamId): string;

    /**
     * Write something to the specified stream.
     *
     * @param bool $appendNewline true (default) appends a new line ("\n") to the end; false - nothing is appended
     */
    public function writeInput(
        string $input,
        int $streamId = ProcessInterface::STDIN,
        bool $appendNewline = true
    ): void;

    /**
     * Send a signal to the running process.
     *
     * @param string $signal One of SIG* constants
     */
    public function sendSignal(string $signal): void;

    /**
     * Returns the process id (pid) of running process.
     *
     * @throws RuntimeException If the process has already exited.
     */
    public function getPid(): int;

    /**
     * Get the command that was executed to create the process.
     */
    public function getCommand(): string;

    /**
     * Return a promise representing the running process.
     */
    public function getPromise(LoopInterface $eventLoop): PromiseInterface;
}
