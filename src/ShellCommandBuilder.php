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
     * @throws \RuntimeException if no Environment is provided and the OS was not known.
     *
     * @param EnvironmentInterface|null $environment If not provided the builder will attempt to find the correct
     *      environment for the OS.
     * @param string $command
     * @param string[] $argumentsList
     * @param int $timeout
     * @param int $pollTimeout
     * @param string $cwd
     * @param ProcessObserverInterface[] $observerList
     */
    public function __construct(
        EnvironmentInterface $environment = null,
        $command = '',
        array $argumentsList = array(),
        $timeout = -1,
        $pollTimeout = 1000,
        $cwd = '',
        array $observerList = array()
    ) {
        $this->command = $command;
        $this->argumentList = $argumentsList;
        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
        $this->cwd = $cwd;
        $this->observerList = $observerList;

        if (is_null($environment)) {
            switch (PHP_OS) {
                case 'Linux':
                case 'Darwin':
                    $environment = new UnixEnvironment();
                    break;

                case 'Windows':
                case 'WINNT':
                case 'WIN32':
                    $environment = new WindowsEnvironment();
                    break;

                default:
                    throw new \RuntimeException(
                        'Unable to find Environment for OS "' . PHP_OS . '".' . PHP_EOL .
                        'Try explicitly providing an Environment when instantiating the builder.'
                    );
                    break;
            }
        }

        $this->environment = $environment;
    }

    /**
     * @inheritDoc
     */
    public function setCommand($command)
    {
        $newBuilder = clone $this;
        $newBuilder->command = $command;

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function addArgument($argument)
    {
        $argumentList = $this->argumentList;
        $argumentList[] = $argument;

        $newBuilder = clone $this;
        $newBuilder->argumentList = $argumentList;

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function addArguments(array $argumentList)
    {
        $argumentList = array_merge($this->argumentList, $argumentList);

        $newBuilder = clone $this;
        $newBuilder->argumentList = $argumentList;

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function setTimeout($timeout)
    {
        $newBuilder = clone $this;
        $newBuilder->timeout = $timeout;

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function setPollTimeout($pollTimeout)
    {
        $newBuilder = clone $this;
        $newBuilder->pollTimeout = $pollTimeout;

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function setCwd($cwd)
    {
        $newBuilder = clone $this;
        $newBuilder->cwd = $cwd;

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function addProcessObserver(ProcessObserverInterface $observer)
    {
        $observerList = $this->observerList;
        $observerList[] = $observer;

        $newBuilder = clone $this;
        $newBuilder->observerList = $observerList;

        return $newBuilder;
    }


    /**
     * @inheritDoc
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

    /**
     * If more than one observer is attached to the command then wrap them up in an Aggregate logger.
     *
     * This means that as far as
     *
     * @return null|ProcessObserverInterface|AggregateLogger
     */
    private function getObserver()
    {
        $observer = null;

        if (1 === count($this->observerList)) {
            $observer = $this->observerList[0];

        } elseif (count($this->observerList)) {
            $observer = new AggregateLogger($this->observerList);
        }

        // TODO: Use null logger!

        return $observer;
    }
}
