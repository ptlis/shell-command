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
use ptlis\ShellCommand\Interfaces\CommandInterface;

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
     * @var string[] The output for the mocked command.
     */
    private $output = array();

    /**
     * @var int The exit code for the mocked command.
     */
    private $exitCode = 0;


    /**
     * Set the binary to execute.
     *
     * @param $binary
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
     * Set the mock output.
     *
     * @param string[] $output
     *
     * @return $this
     */
    public function setOutput(array $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Set the mock exit code.
     *
     * @param int $exitCode
     *
     * @return $this
     */
    public function setExitCode($exitCode)
    {
        $this->exitCode = 0;

        return $this;
    }

    /**
     * Gets the built command & resets the builder.
     *
     * @return CommandInterface
     */
    public function getCommand()
    {
        if (!$this->binary) {
            throw new \RuntimeException('No binary was provided to "' . __CLASS__ . '", unable to build command.');
        }

        $command = new MockCommand(
            $this->binary,
            $this->argumentList,
            $this->output,
            $this->exitCode
        );

        $this->binary = null;
        $this->argumentList = array();
        $this->output = array();
        $this->exitCode = 0;

        return $command;
    }
}
