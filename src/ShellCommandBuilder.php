<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Argument\AdHoc;
use ptlis\ShellCommand\Argument\Argument;
use ptlis\ShellCommand\Argument\Flag;
use ptlis\ShellCommand\Argument\Parameter;
use ptlis\ShellCommand\Exceptions\InvalidBinaryException;
use ptlis\ShellCommand\Interfaces\ArgumentInterface;
use ptlis\ShellCommand\Interfaces\BinaryInterface;
use ptlis\ShellCommand\Interfaces\CommandBuilderInterface;
use ptlis\ShellCommand\Interfaces\SynchronousCommandInterface;

/**
 * Implementation  of shell command builder interface.
 */
class ShellCommandBuilder implements CommandBuilderInterface
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
     * Set the binary to execute.
     *
     * @throws InvalidBinaryException
     *
     * @param $binary
     *
     * @return $this
     */
    public function setBinary($binary)
    {
        $this->binary = new UnixBinary($binary);

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
     * Gets the built command & resets the builder.
     *
     * @return SynchronousCommandInterface
     */
    public function getSynchronousCommand()
    {
        if (!$this->binary) {
            throw new \RuntimeException('No binary was provided to "' . __CLASS__ . '", unable to build command.');
        }

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            $this->binary,
            $this->argumentList
        );

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
