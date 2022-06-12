<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Mock implementation of CommandInterface provided to simplify testing.
 */
final class MockCommand implements CommandInterface
{
    /**
     * @param array<string> $argumentList
     * @param array<string> $rawArgumentList
     */
    public function __construct(
        private readonly string $command,
        private readonly array $argumentList,
        private readonly array $rawArgumentList,
        private readonly ProcessOutputInterface $result,
        private readonly int $runningTime = 314,
        private readonly int $pid = 31415
    ) {
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
