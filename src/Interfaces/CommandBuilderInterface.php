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
 * Builder to create a command ready to execute.
 */
interface CommandBuilderInterface
{
    /**
     * Set the command to execute.
     *
     * @param $command
     *
     * @return $this
     */
    public function setCommand($command);

    /**
     * Add an argument to the command.
     *
     * @param string $argument
     *
     * @return $this
     */
    public function addArgument($argument);

    /**
     * Add one or more arguments to the command.
     *
     * @param string[] $argumentList
     *
     * @return $this
     */
    public function addArguments(array $argumentList);

    /**
     * Set the timeout
     *
     * @param int $timeout (microseconds) How long to wait for a command to finish executing.
     *
     * @return $this
     */
    public function setTimeout($timeout);

    /**
     * Get the build command.
     *
     * @return CommandInterface
     */
    public function buildCommand();
}
