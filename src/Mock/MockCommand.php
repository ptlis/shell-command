<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Mock implementation of CommandInterface provided to simplify testing.
 */
final class MockCommand implements CommandInterface
{
    /** @var EnvironmentInterface */
    private $environment;

    /** @var string */
    private $command;

    /** @var string[] */
    private $argumentList;

    /** @var string[] */
    private $rawArgumentList;

    /** @var ProcessOutputInterface */
    private $result;

    /** @var string[] */
    private $envVariables;

    /** @var int */
    private $runningTime;

    /** @var int */
    private $pid;


    public function __construct(
        EnvironmentInterface $environment,
        string $command,
        array $argumentList,
        array $rawArgumentList,
        ProcessOutputInterface $result,
        ?array $envVariables = [],
        ?int $runningTime = 314,
        ?int $pid = 31415
    ) {
        $this->environment = $environment;
        $this->command = $command;
        $this->argumentList = $argumentList;
        $this->rawArgumentList = $rawArgumentList;
        $this->result = $result;
        $this->envVariables = $envVariables;
        $this->runningTime = $runningTime;
        $this->pid = $pid;
    }

    public function runSynchronous(): ProcessOutputInterface
    {
        return $this->result;
    }

    public function runAsynchronous(): ProcessInterface
    {
        return new MockProcess(
            $this->command,
            $this->result,
            $this->runningTime,
            $this->pid
        );
    }

    public function __toString(): string
    {
        $arguments = '';
        foreach ($this->argumentList as $argument) {
            $arguments .= ' \'' . $argument . '\'';
        }

        foreach ($this->rawArgumentList as $rawArgument) {
            $arguments .= ' ' . $rawArgument;
        }

        return $this->command . $arguments;
    }
}
