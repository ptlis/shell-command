<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandArgumentInterface;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Shell Command, encapsulates the data required to execute a shell command.
 */
final class Command implements CommandInterface
{
    private EnvironmentInterface $environment;
    private string $command;
    /** @var array<CommandArgumentInterface> */
    private array $argumentList;
    private int $timeout;
    private int $pollTimeout;
    private string $cwd;
    /** @var array<string> */
    private array $envVariableList;
    private ProcessObserverInterface $processObserver;

    public function __construct(
        EnvironmentInterface $environment,
        ProcessObserverInterface $processObserver,
        string $command,
        array $newArgumentList,
        string $cwd,
        array $envVariableList = [],
        int $timeout = -1,
        int $pollTimeout = 1000000
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

    public function runSynchronous(): ProcessOutputInterface
    {
        return $this->runAsynchronous()->wait();
    }

    public function runAsynchronous(): ProcessInterface
    {
        return new Process(
            $this->environment,
            (string)$this,
            $this->environment->expandPath($this->cwd),
            $this->envVariableList,
            $this->timeout,
            $this->pollTimeout,
            $this->processObserver
        );
    }

    public function __toString(): string
    {
        $stringCommand = $this->command;
        foreach ($this->argumentList as $argument) {
            $stringCommand .= ' ' . $argument->encode();
        }

        return $stringCommand;
    }
}
