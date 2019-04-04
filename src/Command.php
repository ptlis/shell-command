<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

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
    /** @var EnvironmentInterface */
    private $environment;

    /** @var string */
    private $command;

    /** @var CommandArgumentInterface[] */
    private $argumentList;

    /** @var int*/
    private $timeout;

    /** @var int */
    private $pollTimeout;

    /** @var string */
    private $cwd;

    /** @var string[] */
    private $envVariableList;

    /** @var ProcessObserverInterface */
    private $processObserver;


    public function __construct(
        EnvironmentInterface $environment,
        ProcessObserverInterface $processObserver,
        string $command,
        array $newArgumentList,
        string $cwd,
        array $envVariableList = [],
        int $timeout = -1,
        int $pollTimeout = 1000
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

        return $this->environment->applyEnvironmentVariables($stringCommand, $this->envVariableList);
    }
}
