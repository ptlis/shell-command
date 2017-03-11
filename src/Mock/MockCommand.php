<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Mock implementation of CommandInterface provided to simplify testing.
 */
final class MockCommand implements CommandInterface
{
    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @var string The command to execute.
     */
    private $command;

    /**
     * @var string[] Array of arguments to pass to the command.
     */
    private $argumentList;

    /**
     * @var string[] Array of raw arguments to pass to the command.
     */
    private $rawArgumentList;

    /**
     * @var ProcessOutputInterface The mocked result of this operation.
     */
    private $result;

    /**
     * @var string[] Array of environment variables to set. Key is variable name and value is the variable value.
     */
    private $envVariables;

    /**
     * @var int (microseconds) The amount of time to simulate the process running for.
     */
    private $runningTime;

    /**
     * @var int The simulated process id.
     */
    private $pid;


    /**
     * Constructor
     *
     * @param EnvironmentInterface $environment
     * @param string $command
     * @param string[] $argumentList
     * @param ProcessOutputInterface $result
     * @param int $runningTime
     * @param int $pid
     * @param string[] $envVariables
     */
    public function __construct(
        EnvironmentInterface $environment,
        $command,
        array $argumentList,
        array $rawArgumentList,
        ProcessOutputInterface $result,
        array $envVariables = [],
        $runningTime = 314,
        $pid = 31415
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

    /**
     * {@inheritDoc}
     */
    public function runSynchronous()
    {
        return $this->result;
    }

    /**
     * {@inheritDoc}
     */
    public function runAsynchronous()
    {
        // TODO: Implement this missing class!
        return new MockProcess(
            $this->result->getExitCode(),
            $this->result->getStdOut(),
            $this->result->getStdErr(),
            $this->runningTime,
            $this->pid
        );
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $arguments = '';
        foreach ($this->argumentList as $argument) {
            $arguments .= ' \'' . $argument . '\'';
        }

        foreach ($this->rawArgumentList as $rawArgument) {
            $arguments .= ' ' . $rawArgument;
        }

        return $this->environment->applyEnvironmentVariables($this->command . $arguments, $this->envVariables);
    }
}
