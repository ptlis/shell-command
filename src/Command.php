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
    private readonly string $cwd;

    /**
     * @param array<CommandArgumentInterface> $argumentList
     * @param array<string, string> $envVariableList
     */
    public function __construct(
        private readonly EnvironmentInterface $environment,
        private readonly ProcessObserverInterface $processObserver,
        private readonly string $command,
        private readonly array $argumentList,
        string $cwd,
        private readonly array $envVariableList = [],
        private readonly int $timeout = -1,
        private readonly int $pollTimeout = 1000000
    ) {
        $this->cwd = $this->environment->getNormalizedCwd($cwd);
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
