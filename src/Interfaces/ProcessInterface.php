<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Interfaces;

use ptlis\ShellCommand\Exceptions\CommandExecutionException;

/**
 * Wrapper around proc_* functionality, pipes (etc).
 */
interface ProcessInterface
{
    /**
     * Begin executing the process.
     *
     * @param string $command The command to begin executing.
     *
     * @throws CommandExecutionException
     */
    public function start($command);

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
     * Get the result of a commands execution.
     *
     * @throws \RuntimeException
     *
     * @param string $command
     *
     * @return CommandResultInterface
     */
    public function runSynchronous($command);
}
