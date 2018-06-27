<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandArgumentInterface;
use ptlis\ShellCommand\Interfaces\CommandBuilderInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Logger\AggregateLogger;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Promise\DeferredFactory;
use ptlis\ShellCommand\Promise\ReactDeferredFactory;

/**
 * Immutable builder, used to create Commands.
 */
final class CommandBuilder implements CommandBuilderInterface
{
    /**
     * @var EnvironmentInterface Instance of class that wraps environment-specific behaviours.
     */
    private $environment;

    /**
     * @var DeferredFactory Factory that builds deferred instances.
     */
    private $deferredFactory;

    /**
     * @var string The command to execute.
     */
    private $command;

    /**
     * @var CommandArgumentInterface[] Array of arguments to pass to the command.
     */
    private $argumentList = [];

    /**
     * @var int (microseconds) How long to wait for a command to finish executing, -1 to wait indefinitely.
     */
    private $timeout = -1;

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
     * @var string|int[] Array of environment variables. Array key is the variable name and array value is the env value.
     */
    private $envVariableList = [];

    /**
     * @var ProcessObserverInterface[] List of observers to attach to processes created by built Command.
     */
    private $observerList = [];


    /**
     * Constructor.
     *
     * @throws \RuntimeException if no Environment is provided and the OS was not known.
     *
     * @param EnvironmentInterface|null $environment If not provided the builder will attempt to find the correct
     *      environment for the OS.
     * @param DeferredFactory|null $deferredFactory If not provided a default React promise factory will be used.
     */
    public function __construct(
        EnvironmentInterface $environment = null,
        DeferredFactory $deferredFactory = null
    ) {
        if (is_null($environment)) {
            $environment = $this->getEnvironment(PHP_OS);
        }
        $this->environment = $environment;

        if (is_null($deferredFactory)) {
            $deferredFactory = new ReactDeferredFactory();
        }
        $this->deferredFactory = $deferredFactory;
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
    public function addArgument($argument, $conditionalResult = true)
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $newBuilder->argumentList[] = new CommandArgumentEscaped($argument, $this->environment);
        }

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function addArguments(array $argumentList, $conditionalResult = true)
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            foreach ($argumentList as $argument) {
                $newBuilder->argumentList[] = new CommandArgumentEscaped($argument, $this->environment);
            }
        }

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function addRawArgument($rawArgument, $conditionalResult = true)
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $newBuilder->argumentList[] = new CommandArgumentRaw($rawArgument);
        }

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function addRawArguments(array $rawArgumentList, $conditionalResult = true)
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            foreach ($rawArgumentList as $rawArgument) {
                $newBuilder->argumentList[] = new CommandArgumentRaw($rawArgument);
            }
        }

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
    public function addEnvironmentVariable($key, $value, $conditionalResult = true)
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $envVariableList = $this->envVariableList;
            $envVariableList[$key] = $value;
            $newBuilder->envVariableList = $envVariableList;
        }

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function addEnvironmentVariables(array $envVars, $conditionalResult = true)
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $newBuilder->envVariableList = array_merge($newBuilder->envVariableList, $envVars);
        }

        return $newBuilder;
    }

    /**
     * @inheritDoc
     */
    public function buildCommand()
    {
        if (!$this->environment->validateCommand($this->command)) {
            throw new \RuntimeException('Invalid command "' . $this->command . '" provided to ' . __CLASS__ . '.');
        }

        $cwd = $this->cwd;
        if (!strlen($cwd)) {
            $cwd = getcwd();
        }

        return new Command(
            $this->environment,
            $this->deferredFactory,
            $this->getObserver(),
            $this->command,
            $this->argumentList,
            $cwd,
            $this->envVariableList,
            $this->timeout,
            $this->pollTimeout
        );
    }

    /**
     * If more than one observer is attached to the command then wrap them up in an Aggregate logger.
     *
     * This means that as far as
     *
     * @return ProcessObserverInterface
     */
    private function getObserver()
    {
        if (1 === count($this->observerList)) {
            $observer = $this->observerList[0];
        } elseif (count($this->observerList)) {
            $observer = new AggregateLogger($this->observerList);
        } else {
            $observer = new NullProcessObserver();
        }

        return $observer;
    }

    /**
     * Build the correct Environment instance for the provided operating system.
     *
     * @throws \RuntimeException
     *
     * @param string $operatingSystem A value from PHP_OS
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment($operatingSystem)
    {
        $environmentList = [
            new UnixEnvironment()
        ];

        /** @var EnvironmentInterface $environment */
        $environment = array_reduce(
            $environmentList,
            function (EnvironmentInterface $carry = null, EnvironmentInterface $item) use ($operatingSystem) {
                if (in_array($operatingSystem, $item->getSupportedList())) {
                    $carry = $item;
                }

                return $carry;
            }
        );

        if (is_null($environment)) {
            throw new \RuntimeException(
                'Unable to find Environment for OS "' . $operatingSystem . '".'.
                'Try explicitly providing an Environment when instantiating the builder.'
            );
        }

        return $environment;
    }
}
