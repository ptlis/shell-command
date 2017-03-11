<?php

/**
 * @copyright (c) 2015-2017 brian ridley
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
     * Add an argument to the command that will be applied without escaping.
     *
     * **WARNING** Be very careful about allowing user-submitted data to be passed in here - this could be easily used
     *             to run an arbitrary command.
     *
     * @param string $rawArgument
     *
     * @return $this
     */
    public function addRawArgument($rawArgument);

    /**
     * Add one or more arguments to the command that will be applied without escaping.
     *
     * **WARNING** Be very careful about allowing user-submitted data to be passed in here - this could be easily used
     *             to run an arbitrary command.
     *
     * @param string[] $rawArgumentList
     *
     * @return $this
     */
    public function addRawArguments(array $rawArgumentList);

    /**
     * Set the timeout
     *
     * @param int $timeout (microseconds) How long to wait for a command to finish executing.
     *
     * @return $this
     */
    public function setTimeout($timeout);

    /**
     * Set how long to sleep between polls of the running process when executing synchronously.
     *
     * @param int $pollTimeout
     *
     * @return $this
     */
    public function setPollTimeout($pollTimeout);

    /**
     * Set the current working directory for the command.
     *
     * @param string $cwd
     *
     * @return $this
     */
    public function setCwd($cwd);

    /**
     * Add a logger to attach to built processes.
     *
     * @param ProcessObserverInterface $observer
     *
     * @return $this
     */
    public function addProcessObserver(ProcessObserverInterface $observer);

    /**
     * Add an environment variable for use when running the command
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addEnvironmentVariable($key, $value);

    /**
     * Get the build command.
     *
     * @return CommandInterface
     */
    public function buildCommand();
}
