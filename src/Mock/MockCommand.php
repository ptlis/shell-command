<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Mock implementation of CommandInterface provided to simplify testing.
 */
class MockCommand implements CommandInterface
{
    /**
     * @var string The command to execute.
     */
    private $command;

    /**
     * @var string[] Array of arguments to pass with the command.
     */
    private $argumentList;

    /**
     * @var ProcessOutputInterface The mocked result of this operation.
     */
    private $result;

    /**
     * @var int (microseconds) The amount of time to simulate the process running for.
     */
    private $runningTime;

    /**
     * @var int The simulated process id.
     */
    private $pid;

    /**
     * @var string[] Array of environment variables to set. Key is variable name and value is the variable value.
     */
    private $envVariables;


    /**
     * Constructor
     *
     * @param string $command
     * @param string[] $argumentList
     * @param ProcessOutputInterface $result
     * @param int $runningTime
     * @param int $pid
     * @param string[] $envVariables
     */
    public function __construct(
        $command,
        array $argumentList,
        ProcessOutputInterface $result,
        $runningTime = 314,
        $pid = 31415,
        array $envVariables = array()
    ) {
        $this->command = $command;
        $this->argumentList = $argumentList;
        $this->result = $result;
        $this->runningTime = $runningTime;
        $this->pid = $pid;
        $this->envVariables = $envVariables;
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
        $envVariables = '';
        foreach ($this->envVariables as $envVarName => $envVarValue) {
            $envVariables .= $envVarName . '=\'' . $envVarValue . '\' ';
        }

        $arguments = '';
        foreach ($this->argumentList as $argument) {
            $arguments .= ' \'' . $argument . '\'';
        }

        return $envVariables . $this->command . $arguments;
    }
}
