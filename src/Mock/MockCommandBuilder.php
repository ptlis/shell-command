<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\CommandBuilderInterface;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\ProcessOutput;

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
    private $argumentList = [];

    /**
     * @var int (microseconds) How long to wait for a command to finish executing, -1 to wait indefinitely.
     */
    private $timeout;

    /**
     * @var int The amount of time in milliseconds to sleep for when polling for completion, defaults to 1/100 of a
     *  second.
     */
    private $pollTimeout;

    /**
     * @var string The current working directory to execute the command in.
     */
    private $cwd;

    /**
     * @var string[] Array of environment variables. Array key is the variable name and array value is the env value.
     */
    private $envVariableList = [];

    /**
     * @var ProcessOutput[] Pre-populated list of results to return.
     */
    private $mockResultList = [];

    /**
     * @var MockCommand[] Array of commands built with this builder.
     */
    private $builtCommandList = [];


    /**
     * Constructor.
     *
     * @todo Deal with odd implications of mockResultList being passed in by reference.
     *
     * @param ProcessOutputInterface[] $mockResultList
     * @param string $command
     * @param string[] $argumentsList
     * @param int $timeout
     * @param int $pollTimeout
     * @param string $cwd
     * @param string[] $envVariableList
     * @param CommandInterface[] $builtCommandList
     */
    public function __construct(
        array &$mockResultList = [],
        $command = '',
        array $argumentsList = [],
        $pollTimeout = 1000,
        $timeout = -1,
        $cwd = '',
        $envVariableList = [],
        array &$builtCommandList = []
    ) {
        $this->mockResultList = &$mockResultList;
        $this->command = $command;
        $this->argumentList = $argumentsList;
        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
        $this->cwd = $cwd;
        $this->envVariableList = $envVariableList;
        $this->builtCommandList = &$builtCommandList;
    }


    /**
     * Set the command to execute.
     *
     * @param string $command
     *
     * @return $this
     */
    public function setCommand($command)
    {
        return new MockCommandBuilder(
            $this->mockResultList,
            $command,
            $this->argumentList,
            $this->timeout,
            $this->pollTimeout,
            $this->cwd,
            $this->envVariableList,
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
            $this->mockResultList,
            $this->command,
            $argumentList,
            $this->timeout,
            $this->pollTimeout,
            $this->cwd,
            $this->envVariableList,
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
            $this->mockResultList,
            $this->command,
            $argumentList,
            $this->timeout,
            $this->pollTimeout,
            $this->cwd,
            $this->envVariableList,
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
            $this->mockResultList,
            $this->command,
            $this->argumentList,
            $timeout,
            $this->pollTimeout,
            $this->cwd,
            $this->envVariableList,
            $this->builtCommandList
        );
    }

    /**
     * Set how long to sleep between polls of the running process when executing synchronously.
     *
     * @param int $pollTimeout
     *
     * @return $this
     */
    public function setPollTimeout($pollTimeout)
    {
        return new MockCommandBuilder(
            $this->mockResultList,
            $this->command,
            $this->argumentList,
            $this->timeout,
            $pollTimeout,
            $this->cwd,
            $this->envVariableList,
            $this->builtCommandList
        );
    }

    /**
     * Add a mock result (to be returned in order of execution).
     *
     * @todo Removing this method and switching to constructor injection would allow us to remove the pass-by-references
     *   into constructor and make this class truly immutable.
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
        $mockResultList[] = new ProcessOutput($exitCode, $stdOut, $stdErr);

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

    /**
     * Set the current working directory for the command.
     *
     * @param string $cwd
     *
     * @return $this
     */
    public function setCwd($cwd)
    {
        return new MockCommandBuilder(
            $this->mockResultList,
            $this->command,
            $this->argumentList,
            $this->timeout,
            $this->pollTimeout,
            $cwd,
            $this->envVariableList,
            $this->builtCommandList
        );
    }

    /**
     * Get all commands built by this builder instance.
     *
     * @return MockCommand[]
     */
    public function getBuiltCommands()
    {
        return $this->builtCommandList;
    }

    public function addProcessObserver(ProcessObserverInterface $processObserver)
    {

    }

    public function addEnvironmentVariable($key, $value)
    {
        $envVariableList = $this->envVariableList;
        $envVariableList[$key] = $value;

        return new MockCommandBuilder(
            $this->mockResultList,
            $this->command,
            $this->argumentList,
            $this->timeout,
            $this->pollTimeout,
            $this->cwd,
            $envVariableList,
            $this->builtCommandList
        );
    }

    /**
     * Get the build command.
     *
     * @return CommandInterface
     */
    public function buildCommand()
    {
        if (!$this->command) {
            throw new \RuntimeException('No command was provided to "' . __CLASS__ . '", unable to build command.');
        }

        if (!count($this->mockResultList)) {
            throw new \RuntimeException('No result was provided for use when mocking execution of the command.');
        }

        $result = array_shift($this->mockResultList);

        $command = new MockCommand(
            $this->command,
            $this->argumentList,
            $result,
            $this->envVariableList
        );
        $this->builtCommandList[] = $command;

        return $command;
    }
}
