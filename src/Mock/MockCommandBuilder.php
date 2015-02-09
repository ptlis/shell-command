<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\CommandBuilderInterface;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\CommandResultInterface;
use ptlis\ShellCommand\ShellResult;

/**
 * Mock implementation of the command builder interface.
 */
class MockCommandBuilder implements CommandBuilderInterface
{
    /**
     * @var string The command to execute.
     */
    private $command;

    /**
     * @var string[] Array of arguments to pass to the command.
     */
    private $argumentList = array();

    /**
     * @var int (microseconds) How long to wait for a command to finish executing, -1 to wait indefinitely.
     */
    private $timeout;

    /**
     * @var ShellResult[] Pre-populated list of results to return.
     */
    private $mockResultList = array();

    /**
     * @var MockSynchronousCommand[] Array of commands built with this builder.
     */
    private $builtCommandList = array();


    /**
     * Constructor.
     *
     * @param string $command
     * @param string[] $argumentsList
     * @param int $timeout
     * @param CommandResultInterface[] $mockResultList
     * @param CommandInterface[] &$builtCommandList
     */
    public function __construct(
        $command = '',
        array $argumentsList = array(),
        $timeout = -1,
        array $mockResultList = array(),
        array &$builtCommandList = array()
    ) {
        $this->command = $command;
        $this->argumentList = $argumentsList;
        $this->timeout = $timeout;
        $this->mockResultList = $mockResultList;
        $this->builtCommandList = &$builtCommandList;
    }


    /**
     * Set the binary to execute.
     *
     * @param string $command
     *
     * @return $this
     */
    public function setCommand($command)
    {
        return new MockCommandBuilder(
            $command,
            $this->argumentList,
            $this->timeout,
            $this->mockResultList,
            $this->builtCommandList
        );
    }

    /**
     * Add an argument to the command.
     *
     * @param string $argument
     *
     * @return $this
     */
    public function addArgument($argument)
    {
        $argumentList = $this->argumentList;
        $argumentList[] = $argument;

        return new MockCommandBuilder(
            $this->command,
            $argumentList,
            $this->timeout,
            $this->mockResultList,
            $this->builtCommandList
        );
    }

    /**
     * Add one or more arguments to the command.
     *
     * @param string[] $argumentList
     *
     * @return $this
     */
    public function addArguments(array $argumentList)
    {
        $argumentList = array_merge($this->argumentList, $argumentList);

        return new MockCommandBuilder(
            $this->command,
            $argumentList,
            $this->timeout,
            $this->mockResultList,
            $this->builtCommandList
        );
    }

    /**
     * Set the timeout
     *
     * @param int $timeout (microseconds) How long to wait for a command to finish executing.
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        return new MockCommandBuilder(
            $this->command,
            $this->argumentList,
            $timeout,
            $this->mockResultList,
            $this->builtCommandList
        );
    }

    /**
     * Add a mock result (to be returned in order of execution).
     *
     * @param int $exitCode
     * @param string $stdOut
     * @param string $stdErr
     *
     * @return $this
     */
    public function addMockResult($exitCode, $stdOut, $stdErr)
    {
        $mockResultList = $this->mockResultList;
        $mockResultList[] = new ShellResult($exitCode, $stdOut, $stdErr);

        return new MockCommandBuilder(
            $this->command,
            $this->argumentList,
            $this->timeout,
            $mockResultList,
            $this->builtCommandList
        );
    }

    /**
     * Get all commands built by this builder instance.
     *
     * @return MockSynchronousCommand[]
     */
    public function getBuiltCommands()
    {
        return $this->builtCommandList;
    }

    /**
     * Get the build command.
     *
     * @return CommandInterface
     */
    public function buildCommand()
    {
        if (!$this->command) {
            throw new \RuntimeException('No binary was provided to "' . __CLASS__ . '", unable to build command.');
        }

        if (!count($this->mockResultList)) {
            throw new \RuntimeException('No result was provided for use when mocking execution of the command.');
        }

        $result = array_shift($this->mockResultList);

        $command = new MockSynchronousCommand(
            $this->command,
            $this->argumentList,
            $result
        );
        $this->builtCommandList[] = $command;

        return $command;
    }
}
