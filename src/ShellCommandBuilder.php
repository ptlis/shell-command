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
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Logger\AggregateLogger;

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
     * @var string The current working directory to execute the command in.
     */
    private $cwd;

    /**
     * @var ProcessObserverInterface[] List of observers to attach to running processed created in built Command.
     */
    private $observerList;


    /**
     * Constructor.
     *
     * @param EnvironmentInterface $environment
     * @param string $command
     * @param array $argumentsList
     * @param int $timeout
     * @param int $pollTimeout
     * @param string $cwd
     * @param ProcessObserverInterface[] $observerList
     */
    public function __construct(
        EnvironmentInterface $environment,
        $command = '',
        array $argumentsList = array(),
        $timeout = -1,
        $pollTimeout = 1000,
        $cwd = '',
        array $observerList = array()
    ) {
        $this->environment = $environment;
        $this->command = $command;
        $this->argumentList = $argumentsList;
        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
        $this->cwd = $cwd;
        $this->observerList = $observerList;
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
            $this->pollTimeout,
            $this->cwd,
            $this->observerList
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
            $this->pollTimeout,
            $this->cwd,
            $this->observerList
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
            $this->pollTimeout,
            $this->cwd,
            $this->observerList
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
            $this->pollTimeout,
            $this->cwd,
            $this->observerList
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
            $pollTimeout,
            $this->cwd,
            $this->observerList
        );
    }

    /**
     * Set the current working directory for the command.
     *
     * @param string $cwd
     *
     * @return $this
     */
    public function setCwd($cwd)
    {
        return new ShellCommandBuilder(
            $this->environment,
            $this->command,
            $this->argumentList,
            $this->timeout,
            $this->pollTimeout,
            $cwd,
            $this->observerList
        );
    }

    /**
     * Add a logger to attach to running processes.
     *
     * @param ProcessObserverInterface $observer
     *
     * @return $this;
     */
    public function addProcessObserver(ProcessObserverInterface $observer)
    {
        $observerList = $this->observerList;
        $observerList[] = $observer;

        return new ShellCommandBuilder(
            $this->environment,
            $this->command,
            $this->argumentList,
            $this->timeout,
            $this->pollTimeout,
            $this->cwd,
            $observerList
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

        $cwd = $this->cwd;
        if (!strlen($cwd)) {
            $cwd = getcwd();
        }

        return new ShellCommand(
            $this->environment,
            $this->command,
            $this->argumentList,
            $cwd,
            $this->timeout,
            $this->pollTimeout,
            $this->getObserver()
        );
    }

    private function getObserver()
    {
        $observer = null;

        if (1 === count($this->observerList)) {
            $observer = $this->observerList[0];

        } elseif (count($this->observerList)) {
            $observer = new AggregateLogger($this->observerList);
        }

        return $observer;
    }
}
