<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\CommandBuilderInterface;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\ProcessOutput;
use RuntimeException;

/**
 * Mock implementation of the command builder interface.
 */
final class MockCommandBuilder implements CommandBuilderInterface
{
    /**
     * @param array<ProcessOutput> $mockResultList
     * @param array<string> $argumentList
     * @param array<string, string> $envVariableList
     * @param array<MockCommand> $builtCommandList
     * @param array<string> $rawArgumentList
     * @param array<ProcessObserverInterface> $observerList
     */
    public function __construct(
        private array $mockResultList = [],
        private string $command = '',
        private array $argumentList = [],
        private int $pollTimeout = 1000,
        private int $timeout = -1,
        private string $cwd = '',
        private array $envVariableList = [],
        private array &$builtCommandList = [],
        private array $rawArgumentList = [],
        private array $observerList = []
    ) {
    }

    public function setCommand(string $command): MockCommandBuilder
    {
        $newBuilder = clone $this;
        $newBuilder->builtCommandList = &$this->builtCommandList;
        $newBuilder->mockResultList = &$this->mockResultList;
        $newBuilder->command = $command;

        return $newBuilder;
    }

    public function addArgument(string $argument, bool $conditionalResult = true): MockCommandBuilder
    {
        $newBuilder = clone $this;
        $newBuilder->builtCommandList = &$this->builtCommandList;
        $newBuilder->mockResultList = &$this->mockResultList;

        if ($conditionalResult) {
            $argumentList = $this->argumentList;
            $argumentList[] = $argument;
            $newBuilder->argumentList = $argumentList;
        }

        return $newBuilder;
    }

    public function addArguments(array $argumentList, bool $conditionalResult = true): MockCommandBuilder
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $argumentList = \array_merge($this->argumentList, $argumentList);
            $newBuilder->builtCommandList = &$this->builtCommandList;
            $newBuilder->mockResultList = &$this->mockResultList;
            $newBuilder->argumentList = $argumentList;
        }

        return $newBuilder;
    }

    public function addRawArgument(string $rawArgument, bool $conditionalResult = true): MockCommandBuilder
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $rawArgumentList = $this->rawArgumentList;
            $rawArgumentList[] = $rawArgument;
            $newBuilder->builtCommandList = &$this->builtCommandList;
            $newBuilder->mockResultList = &$this->mockResultList;
            $newBuilder->rawArgumentList = $rawArgumentList;
        }

        return $newBuilder;
    }

    public function addRawArguments(array $rawArgumentList, bool $conditionalResult = true): MockCommandBuilder
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $rawArgumentList = array_merge($this->rawArgumentList, $rawArgumentList);
            $newBuilder->builtCommandList = &$this->builtCommandList;
            $newBuilder->mockResultList = &$this->mockResultList;
            $newBuilder->rawArgumentList = $rawArgumentList;
        }

        return $newBuilder;
    }

    public function setTimeout(int $timeout): MockCommandBuilder
    {
        $newBuilder = clone $this;
        $newBuilder->builtCommandList = &$this->builtCommandList;
        $newBuilder->mockResultList = &$this->mockResultList;
        $newBuilder->timeout = $timeout;

        return $newBuilder;
    }

    public function setPollTimeout(int $pollTimeout): MockCommandBuilder
    {
        $newBuilder = clone $this;
        $newBuilder->builtCommandList = &$this->builtCommandList;
        $newBuilder->mockResultList = &$this->mockResultList;
        $newBuilder->pollTimeout = $pollTimeout;

        return $newBuilder;
    }

    public function setCwd(string $cwd): MockCommandBuilder
    {
        $newBuilder = clone $this;
        $newBuilder->builtCommandList = &$this->builtCommandList;
        $newBuilder->mockResultList = &$this->mockResultList;
        $newBuilder->cwd = $cwd;

        return $newBuilder;
    }

    /**
     * @return array<MockCommand>
     */
    public function getBuiltCommands(): array
    {
        return $this->builtCommandList;
    }

    public function addProcessObserver(ProcessObserverInterface $observer): MockCommandBuilder
    {
        $observerList = $this->observerList;
        $observerList[] = $observer;

        $newBuilder = clone $this;
        $newBuilder->builtCommandList = &$this->builtCommandList;
        $newBuilder->mockResultList = &$this->mockResultList;
        $newBuilder->observerList = $observerList;

        return $newBuilder;
    }

    public function addEnvironmentVariable(
        string $key,
        string $value,
        bool $conditionalResult = true
    ): MockCommandBuilder {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $envVariableList = $this->envVariableList;
            $envVariableList[$key] = $value;
            $newBuilder->builtCommandList = &$this->builtCommandList;
            $newBuilder->mockResultList = &$this->mockResultList;
            $newBuilder->envVariableList = $envVariableList;
        }

        return $newBuilder;
    }

    public function addEnvironmentVariables(array $envVars, bool $conditionalResult = true): MockCommandBuilder
    {
        $newBuilder = clone $this;

        if ($conditionalResult) {
            $envVariableList = array_merge($this->envVariableList, $envVars);
            $newBuilder->builtCommandList = &$this->builtCommandList;
            $newBuilder->mockResultList = &$this->mockResultList;
            $newBuilder->envVariableList = $envVariableList;
        }

        return $newBuilder;
    }

    public function buildCommand(): CommandInterface
    {
        if (!$this->command) {
            throw new RuntimeException('No command was provided to "' . __CLASS__ . '", unable to build command.');
        }

        if (!count($this->mockResultList)) {
            throw new RuntimeException('No result was provided for use when mocking execution of the command.');
        }

        $result = \array_shift($this->mockResultList);

        $command = new MockCommand(
            $this->command,
            $this->argumentList,
            $this->rawArgumentList,
            $result
        );
        $this->builtCommandList[] = $command;

        return $command;
    }

    /**
     * Add a mock result (to be returned in order of execution).
     */
    public function addMockResult(
        int $exitCode,
        string $stdOut,
        string $stdErr,
        string $command,
        string $workingDirectory
    ): MockCommandBuilder {
        $mockResultList = $this->mockResultList;
        $mockResultList[] = new ProcessOutput($exitCode, $stdOut, $stdErr, $command, $workingDirectory);

        return new MockCommandBuilder(
            $mockResultList,
            $this->command,
            $this->argumentList,
            $this->timeout,
            $this->pollTimeout,
            $this->cwd,
            $this->envVariableList,
            $this->builtCommandList
        );
    }
}
