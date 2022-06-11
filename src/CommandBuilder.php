<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandArgumentInterface;
use ptlis\ShellCommand\Interfaces\CommandBuilderInterface;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Logger\AggregateLogger;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use RuntimeException;

/**
 * Immutable builder, used to create Commands.
 */
final class CommandBuilder implements CommandBuilderInterface
{
    private EnvironmentInterface $environment;
    private string $command;
    /** @var array<CommandArgumentInterface> */
    private array $argumentList = [];
    private int $timeout = -1;
    private int $pollTimeout = 1000;
    private string $cwd = '';
    /** @var array<string, string> */
    private array $envVariableList = [];
    /** @var array<ProcessObserverInterface> */
    private array $observerList = [];

    public function __construct(EnvironmentInterface $environment = null)
    {
        if (\is_null($environment)) {
            $environment = $this->getEnvironment(PHP_OS);
        }
        $this->environment = $environment;
    }

    public function setCommand(string $command): CommandBuilderInterface
    {
        $newBuilder = clone $this;
        $newBuilder->command = $command;

        return $newBuilder;
    }

    public function addArgument(string $argument, bool $conditionalResult = true): CommandBuilderInterface
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $newBuilder->argumentList[] = new CommandArgumentEscaped($argument, $this->environment);
        }

        return $newBuilder;
    }

    public function addArguments(array $argumentList, bool $conditionalResult = true): CommandBuilderInterface
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            foreach ($argumentList as $argument) {
                $newBuilder->argumentList[] = new CommandArgumentEscaped($argument, $this->environment);
            }
        }

        return $newBuilder;
    }

    public function addRawArgument(string $rawArgument, bool $conditionalResult = true): CommandBuilderInterface
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $newBuilder->argumentList[] = new CommandArgumentRaw($rawArgument);
        }

        return $newBuilder;
    }

    public function addRawArguments(array $rawArgumentList, bool $conditionalResult = true): CommandBuilderInterface
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            foreach ($rawArgumentList as $rawArgument) {
                $newBuilder->argumentList[] = new CommandArgumentRaw($rawArgument);
            }
        }

        return $newBuilder;
    }

    public function setTimeout(int $timeout): CommandBuilderInterface
    {
        $newBuilder = clone $this;
        $newBuilder->timeout = $timeout;

        return $newBuilder;
    }

    public function setPollTimeout(int $pollTimeout): CommandBuilderInterface
    {
        $newBuilder = clone $this;
        $newBuilder->pollTimeout = $pollTimeout;

        return $newBuilder;
    }

    public function setCwd(string $cwd): CommandBuilderInterface
    {
        $newBuilder = clone $this;
        $newBuilder->cwd = $cwd;

        return $newBuilder;
    }

    public function addProcessObserver(ProcessObserverInterface $observer): CommandBuilderInterface
    {
        $observerList = $this->observerList;
        $observerList[] = $observer;

        $newBuilder = clone $this;
        $newBuilder->observerList = $observerList;

        return $newBuilder;
    }

    public function addEnvironmentVariable(
        string $key,
        string $value,
        bool $conditionalResult = true
    ): CommandBuilderInterface {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $envVariableList = $this->envVariableList;
            $envVariableList[$key] = $value;
            $newBuilder->envVariableList = $envVariableList;
        }

        return $newBuilder;
    }

    public function addEnvironmentVariables(array $envVars, bool $conditionalResult = true): CommandBuilderInterface
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $newBuilder->envVariableList = array_merge($newBuilder->envVariableList, $envVars);
        }

        return $newBuilder;
    }

    public function buildCommand(): CommandInterface
    {
        if (!$this->environment->validateCommand($this->command, $this->cwd)) {
            throw new RuntimeException('Invalid command "' . $this->command . '" provided to ' . __CLASS__ . '.');
        }

        return new Command(
            $this->environment,
            $this->getObserver(),
            $this->command,
            $this->argumentList,
            $this->cwd,
            $this->envVariableList,
            $this->timeout,
            $this->pollTimeout
        );
    }

    /**
     * If more than one observer is attached to the command then wrap them up in an Aggregate logger.
     *
     * This means that as far as calling code is called there's only one observer.
     */
    private function getObserver(): ProcessObserverInterface
    {
        if (1 === \count($this->observerList)) {
            $observer = $this->observerList[0];
        } elseif (\count($this->observerList)) {
            $observer = new AggregateLogger($this->observerList);
        } else {
            $observer = new NullProcessObserver();
        }

        return $observer;
    }

    /**
     * Build the correct Environment instance for the provided operating system.
     */
    public function getEnvironment(string $operatingSystem): EnvironmentInterface
    {
        $environmentList = [
            new UnixEnvironment()
        ];

        /** @var ?EnvironmentInterface $environment */
        $environment = null;
        foreach ($environmentList as $possibleEnvironment) {
            if (\in_array($operatingSystem, $possibleEnvironment->getSupportedList())) {
                $environment = $possibleEnvironment;
            }
        }

        if (is_null($environment)) {
            throw new RuntimeException(
                'Unable to find Environment for OS "' . $operatingSystem . '".' .
                'Try explicitly providing an Environment when instantiating the builder.'
            );
        }

        return $environment;
    }
}
