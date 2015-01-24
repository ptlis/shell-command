<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Argument\AdHoc;
use ptlis\ShellCommand\Argument\Argument;
use ptlis\ShellCommand\Argument\Flag;
use ptlis\ShellCommand\Argument\Parameter;
use ptlis\ShellCommand\Interfaces\ArgumentInterface;
use ptlis\ShellCommand\Interfaces\BinaryInterface;
use ptlis\ShellCommand\Interfaces\CommandBuilderInterface;
use ptlis\ShellCommand\Interfaces\SynchronousCommandInterface;
use ptlis\ShellCommand\ShellResult;

/**
 * Mock implementation of the command builder interface.
 */
class MockCommandBuilder implements CommandBuilderInterface
{
    /**
     * @var BinaryInterface The binary to execute.
     */
    private $binary;

    /**
     * @var ArgumentInterface[] Array of arguments to pass to the binary.
     */
    private $argumentList = array();

    /**
     * @var ShellResult[] Pre-populated list of results to return.
     */
    private $mockResultList = array();

    /**
     * @var MockSynchronousCommand[] Array of commands built with this builder.
     */
    private $builtCommandList = array();


    /**
     * Set the binary to execute.
     *
     * @param string $binary
     *
     * @return $this
     */
    public function setBinary($binary)
    {
        $this->binary = new MockBinary($binary);

        return $this;
    }

    /**
     * Add an argument to the command.
     *
     * @param string $argument
     * @param string $value
     * @param string $separator
     *
     * @return $this
     */
    public function addArgument($argument, $value = '', $separator = ArgumentInterface::SEPARATOR_SPACE)
    {
        $this->argumentList[] = new Argument($argument, $value, $separator);

        return $this;
    }

    /**
     * Add a flag to the command.
     *
     * @param string $flag
     * @param string $value
     *
     * @return $this
     */
    public function addFlag($flag, $value = '')
    {
        $this->argumentList[] = new Flag($flag, $value);

        return $this;
    }

    /**
     * Add a parameter to the command.
     *
     * @param string $parameter
     *
     * @return $this
     */
    public function addParameter($parameter)
    {
        $this->argumentList[] = new Parameter($parameter);

        return $this;
    }

    /**
     * Add an ad-hoc argument, useful for non-standard and old commands.
     *
     * @param string $argument
     *
     * @return $this
     */
    public function addAdHoc($argument)
    {
        $this->argumentList[] = new AdHoc($argument);

        return $this;
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
        $this->mockResultList[] = new ShellResult($exitCode, $stdOut, $stdErr);

        return $this;
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
     * Gets the built command & resets the builder.
     *
     * @return SynchronousCommandInterface
     */
    public function getSynchronousCommand()
    {
        if (!$this->binary) {
            throw new \RuntimeException('No binary was provided to "' . __CLASS__ . '", unable to build command.');
        }

        if (!count($this->mockResultList)) {
            throw new \RuntimeException('No result was provided for use when mocking execution of the command.');
        }

        $result = array_shift($this->mockResultList);

        $command = new MockSynchronousCommand(
            $this->binary,
            $this->argumentList,
            $result
        );
        $this->builtCommandList[] = $command;

        $this->clear();

        return $command;
    }

    /**
     * Clear & reset the builder to default state.
     */
    public function clear()
    {
        $this->binary = null;
        $this->argumentList = array();
    }
}
