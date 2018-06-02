<?php

/**
 * @copyright (c) 2015-2018 brian ridley
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
     * Add an argument to the command if $conditionalResult is true. If the $conditionalResult parameter isn't passed it
     * will always add that argument.
     *
     * @param string $argument
     * @param bool $conditionalResult
     *
     * @return $this
     */
    public function addArgument($argument, $conditionalResult = true);

    /**
     * Add one or more arguments to the command.
     *
     * @param string[] $argumentList
     * @param bool $conditionalResult
     *
     * @return $this
     */
    public function addArguments(array $argumentList, $conditionalResult = true);

    /**
     * Add an argument to the command that will be applied without escaping.
     *
     * **WARNING** Be very careful about allowing user-submitted data to be passed in here - this could be easily used
     *             to run an arbitrary command.
     *
     * @param string $rawArgument
     * @param bool $conditionalResult
     *
     * @return $this
     */
    public function addRawArgument($rawArgument, $conditionalResult = true);

    /**
     * Add one or more arguments to the command that will be applied without escaping.
     *
     * **WARNING** Be very careful about allowing user-submitted data to be passed in here - this could be easily used
     *             to run an arbitrary command.
     *
     * @param string[] $rawArgumentList
     * @param bool $conditionalResult
     *
     * @return $this
     */
    public function addRawArguments(array $rawArgumentList, $conditionalResult = true);

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
     * @param bool $conditionalResult
     *
     * @return $this
     */
    public function addEnvironmentVariable($key, $value, $conditionalResult = true);

    /**
     * Add an array of environment variables for use when running the command
     *
     * @param string|int[] $envVars
     * @param bool $conditionalResult
     *
     * @return $this
     */
    public function addEnvironmentVariables(array $envVars, $conditionalResult = true);

    /**
     * Get the build command.
     *
     * @return CommandInterface
     */
    public function buildCommand();
}
