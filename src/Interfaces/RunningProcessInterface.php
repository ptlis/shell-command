<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Interfaces;

/**
 * Interface defining a way of monitoring an asynchronously running process.
 */
interface RunningProcessInterface
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
    public function isRunning();

    /**
     * Blocks execution until the command has finished executing.
     *
     * @param \Closure|null $callback Execute when the wait time has elapsed, is provided the latest contents of stdout and
     *  stderr.
     */
    public function wait(\Closure $callback = null);

    /**
     * Stops the running process.
     *
     * @param int $timeout (microseconds)
     */
    public function stop($timeout = 1000000);

    /**
     * Read the pending output from the specified stream.
     *
     * @param int $streamId
     *
     * @return string
     */
    public function readOutput($streamId);

    /**
     * Send a signal to the running process.
     *
     * @param int $signal
     */
    public function sendSignal($signal);

    /**
     * Get the exit code of the running process.
     *
     * @throws \RuntimeException If the process has not yet exited.
     *
     * @return int
     */
    public function getExitCode();

    /**
     * Returns the PID (process id) of running process.
     *
     * @throws \RuntimeException If the process has already exited.
     *
     * @return int
     */
    public function getPid();

    /**
     * Get the command that was executed to create the process.
     *
     * @return string
     */
    public function getCommand();
}
