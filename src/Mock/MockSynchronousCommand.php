<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\SynchronousCommandInterface;
use ptlis\ShellCommand\Interfaces\CommandResultInterface;
use ptlis\ShellCommand\ShellResult;

/**
 * Mock implementation of the command interface.
 */
class MockSynchronousCommand implements SynchronousCommandInterface
{
    /**
     * @var string The command to execute.
     */
    private $binary;

    /**
     * @var string[] Array of arguments to pass to the binary.
     */
    private $argumentList = array();

    /**
     * @var ShellResult The mocked result of this operation.
     */
    private $result;


    /**
     * Constructor.
     *
     * @param string $binary
     * @param string[] $argumentList
     * @param ShellResult $result
     */
    public function __construct(
        $binary,
        array $argumentList,
        ShellResult $result
    ) {
        $this->binary = $binary;
        $this->argumentList = $argumentList;
        $this->result = $result;
    }

    /**
     * Execute the command and return its result.
     *
     * @return CommandResultInterface
     */
    public function run()
    {
        return $this->result;
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
            $this->binary
        );
    }
}
