<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\ArgumentInterface;
use ptlis\ShellCommand\Interfaces\BinaryInterface;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\CommandResultInterface;
use ptlis\ShellCommand\ShellResult;

/**
 * Mock implementation of the command interface.
 */
class MockCommand implements CommandInterface
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
     * Constructor.
     *
     * @param BinaryInterface $binary
     * @param ArgumentInterface[] $argumentList
     * @param string[] $output
     * @param int $exitCode
     */
    public function __construct(
        BinaryInterface $binary,
        array $argumentList,
        array $output,
        $exitCode
    ) {
        $this->binary = $binary;
        $this->argumentList = $argumentList;
        $this->output = $output;
        $this->exitCode = $exitCode;
    }

    /**
     * Execute the command and return its result.
     *
     * @return CommandResultInterface
     */
    public function run()
    {
        return new ShellResult(
            $this->exitCode,
            $this->output
        );
    }

    /**
     * Gets the string representation of the command, ready to execute.
     *
     * @return string
     */
    public function __toString()
    {
        return array_reduce(
            $this->argumentList,
            function ($string, $argument) {
                return $string . ' ' . escapeshellarg($argument);
            },
            $this->binary->__toString()
        );
    }
}
