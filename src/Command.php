<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandArgumentInterface;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;

/**
 * Shell Command, encapsulates the data required to execute a shell command.
 */
final class Command implements CommandInterface
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
     * @var CommandArgumentInterface[] Array of arguments to pass with the command.
     */
    private $argumentList;

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
     * @var string[]
     */
    private $envVariableList;

    /**
     * @var ProcessObserverInterface
     */
    private $processObserver;


    /**
     * Constructor
     *
     * @param EnvironmentInterface $environment
     * @param ProcessObserverInterface $processObserver
     * @param string $command
     * @param CommandArgumentInterface[] $newArgumentList
     * @param string $cwd
     * @param string[] $envVariableList
     * @param int $timeout
     * @param int $pollTimeout
     */
    public function __construct(
        EnvironmentInterface $environment,
        ProcessObserverInterface $processObserver,
        $command,
        array $newArgumentList,
        $cwd,
        $envVariableList = [],
        $timeout = -1,
        $pollTimeout = 1000
    ) {
        $this->environment = $environment;
        $this->processObserver = $processObserver;
        $this->command = $command;
        $this->argumentList = $newArgumentList;
        $this->timeout = $timeout;
        $this->envVariableList = $envVariableList;
        $this->pollTimeout = $pollTimeout;
        $this->cwd = $cwd;
    }

    /**
     * @inheritDoc
     */
    public function runSynchronous()
    {
        return $this->runAsynchronous()->wait();
    }

    /**
     * @inheritDoc
     */
    public function runAsynchronous()
    {
        return new Process(
            $this->environment,
            $this,
            $this->environment->expandPath($this->cwd),
            $this->timeout,
            $this->pollTimeout,
            $this->processObserver
        );
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $stringCommand = array_reduce(
            $this->argumentList,
            function ($string, CommandArgumentInterface $argument) {
                return $string . ' ' . $argument->encode();
            },
            $this->command
        );

        return $this->environment->applyEnvironmentVariables($stringCommand, $this->envVariableList);
    }
}
