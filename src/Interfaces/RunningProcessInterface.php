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
    /**
     * Returns true if the process is still running.
     *
     * @return bool
     */
    public function isRunning();

    /**
     * Blocks execution until the command has finished executing.
     */
    public function wait();

    /**
     * Stops the running process.
     */
    public function stop();

    /**
     * Read the pending output from the specified stream.
     *
     * @param int $streamId
     *
     * @return string
     */
    public function readOutput($streamId);

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
}
