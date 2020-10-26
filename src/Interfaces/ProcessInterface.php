<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Interfaces;

use React\EventLoop\LoopInterface;
use React\Promise\Promise;

/**
 * Interface defining a class to monitor process lifetimes.
 */
interface ProcessInterface
{
    /** Signal used to request that the process terminates. */
    const SIGTERM = 'SIGTERM';

    /** Signal used to force the process to terminate. */
    const SIGKILL = 'SIGKILL';

    const STDIN = 0;
    const STDOUT = 1;
    const STDERR = 2;

    /**
     * Returns true if the process is still running.
     *
     * @return bool
     */
    public function isRunning(): bool;

    /**
     * Blocks execution until the command has finished executing.
     *
     * @param \Closure|null $callback Execute when the wait time has elapsed, is provided the latest contents of stdout and
     *  stderr.
     * @return ProcessOutputInterface
     */
    public function wait(\Closure $callback = null): ProcessOutputInterface;

    /**
     * Stops the running process.
     *
     * @param int $timeout (microseconds)
     * @return ProcessOutputInterface
     */
    public function stop(int $timeout = 1000000): ProcessOutputInterface;

    /**
     * Read the pending output from the specified stream.
     *
     * @param int $streamId
     *
     * @return string
     */
    public function readOutput(int $streamId): string;

    /**
     * Write something to the specified stream.
     *
     * @param string $input
     *
     * @param int    $streamId defaults to ProcessInterface::STDIN
     *
     * @param bool $appendNewline true (default) appends a new line ("\n") to the end;
     *                            false - nothing is appended
     */
    public function writeInput(string $input, int $streamId = ProcessInterface::STDIN, bool $appendNewline = true): void;

    /**
     * Send a signal to the running process.
     *
     * @param string $signal One of SIG* constants
     */
    public function sendSignal(string $signal): void;

    /**
     * Returns the PID (process id) of running process.
     *
     * @throws \RuntimeException If the process has already exited.
     *
     * @return int
     */
    public function getPid(): int;

    /**
     * Get the command that was executed to create the process.
     *
     * @return string
     */
    public function getCommand(): string;

    /**
     * Return a promise representing the running process.
     *
     * @param LoopInterface $eventLoop
     * @return Promise
     */
    public function getPromise(LoopInterface $eventLoop): Promise;
}
