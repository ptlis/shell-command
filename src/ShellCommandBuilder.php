<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandBuilderInterface;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;

/**
 * Immutable builder, used to create ShellCommands.
 */
class ShellCommandBuilder implements CommandBuilderInterface
{
    /**
     * @var EnvironmentInterface Instance of class that wraps environment-specific behaviours.
     */
    private $environment;

    /**
     * @var string The command to execute.
     */
    private $command;

    /**
     * @var string[] Array of arguments to pass to the command.
     */
    private $argumentList = array();

    /**
     * @var int (microseconds) How long to wait for a command to finish executing, -1 to wait indefinitely.
     */
    private $timeout;

    /**
     * @var int The amount of time in milliseconds to sleep for when polling for completion, defaults to 1/100 of a
     *  second.
     */
    private $pollTimeout;


    /**
     * Constructor.
     *
     * @param EnvironmentInterface $environment
     * @param string $command
     * @param array $argumentsList
     * @param int $timeout
     * @param int $pollTimeout
     */
    public function __construct(
        EnvironmentInterface $environment,
        $command = '',
        array $argumentsList = array(),
        $timeout = -1,
        $pollTimeout = 1000
    ) {
        $this->environment = $environment;
        $this->command = $command;
        $this->argumentList = $argumentsList;
        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
    }

    /**
     * Set the command to execute.
     *
     * @param $command
     *
     * @return $this
     */
    public function setCommand($command)
    {
        return new ShellCommandBuilder(
            $this->environment,
            $command,
            $this->argumentList,
            $this->timeout,
            $this->pollTimeout
        );
    }

    /**
     * Add an argument to the command.
     *
     * @param string $argument
     *
     * @return $this
     */
    public function addArgument($argument)
    {
        $argumentList = $this->argumentList;
        $argumentList[] = $argument;

        return new ShellCommandBuilder(
            $this->environment,
            $this->command,
            $argumentList,
            $this->timeout,
            $this->pollTimeout
        );
    }

    /**
     * Add one or more arguments to the command.
     *
     * @param string[] $argumentList
     *
     * @return $this
     */
    public function addArguments(array $argumentList)
    {
        $argumentList = array_merge($this->argumentList, $argumentList);

        return new ShellCommandBuilder(
            $this->environment,
            $this->command,
            $argumentList,
            $this->timeout,
            $this->pollTimeout
        );
    }

    /**
     * Set the timeout
     *
     * @param int $timeout (microseconds) How long to wait for a command to finish executing.
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        return new ShellCommandBuilder(
            $this->environment,
            $this->command,
            $this->argumentList,
            $timeout,
            $this->pollTimeout
        );
    }

    /**
     * Set how long to sleep between polls of the running process when executing synchronously.
     *
     * @param int $pollTimeout
     *
     * @return $this
     */
    public function setPollTimeout($pollTimeout)
    {
        return new ShellCommandBuilder(
            $this->environment,
            $this->command,
            $this->argumentList,
            $this->timeout,
            $pollTimeout
        );
    }

    /**
     * Get the build command
     *
     * @return CommandInterface
     */
    public function buildCommand()
    {
        if (!$this->environment->validateCommand($this->command)) {
            throw new \RuntimeException('Invalid command "' . $this->command . '" provided.');
        }

        return new ShellCommand(
            $this->command,
            $this->argumentList,
            $this->pollTimeout
        );
    }
}
