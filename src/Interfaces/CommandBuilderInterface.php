<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Interfaces;

/**
 * Builder to create a command ready to execute.
 */
interface CommandBuilderInterface
{
    /**
     * Set the command to execute.
     */
    public function setCommand(string $command): CommandBuilderInterface;

    /**
     * Add an argument to the command if $conditionalResult is true. If the $conditionalResult parameter isn't passed it
     * will always add that argument.
     */
    public function addArgument(string $argument, bool $conditionalResult = true): CommandBuilderInterface;

    /**
     * Add one or more arguments to the command.
     *
     * @param array<string> $argumentList
     */
    public function addArguments(array $argumentList, bool $conditionalResult = true): CommandBuilderInterface;

    /**
     * Add an argument to the command that will be applied without escaping.
     *
     * **WARNING** Be very careful about allowing user-submitted data to be passed in here - this could be easily used
     *             to run an arbitrary command.
     */
    public function addRawArgument(string $rawArgument, bool $conditionalResult = true): CommandBuilderInterface;

    /**
     * Add one or more arguments to the command that will be applied without escaping.
     *
     * **WARNING** Be very careful about allowing user-submitted data to be passed in here - this could be easily used
     *             to run an arbitrary command.
     *
     * @param array<string> $rawArgumentList
     */
    public function addRawArguments(array $rawArgumentList, bool $conditionalResult = true): CommandBuilderInterface;

    /**
     * Set the timeout
     *
     * @param int $timeout (microseconds) How long to wait for a command to finish executing.
     */
    public function setTimeout(int $timeout): CommandBuilderInterface;

    /**
     * Set how long to sleep between polls of the running process when executing synchronously.
     */
    public function setPollTimeout(int $pollTimeout): CommandBuilderInterface;

    /**
     * Set the current working directory for the command.
     */
    public function setCwd(string $cwd): CommandBuilderInterface;

    /**
     * Add a logger to attach to built processes.
     */
    public function addProcessObserver(ProcessObserverInterface $observer): CommandBuilderInterface;

    /**
     * Add an environment variable for use when running the command.
     */
    public function addEnvironmentVariable(
        string $key,
        string $value,
        bool $conditionalResult = true
    ): CommandBuilderInterface;

    /**
     * Add an array of environment variables for use when running the command
     *
     * @param array<string, string> $envVars
     */
    public function addEnvironmentVariables(array $envVars, bool $conditionalResult = true): CommandBuilderInterface;

    /**
     * Get the build command.
     */
    public function buildCommand(): CommandInterface;
}
