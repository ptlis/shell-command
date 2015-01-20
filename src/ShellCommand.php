<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\ArgumentInterface;
use ptlis\ShellCommand\Interfaces\BinaryInterface;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\CommandResultInterface;

/**
 * Shell Command, encapsulates the data required to execute a shell command.
 */
class ShellCommand implements CommandInterface
{
    /**
     * @var BinaryInterface The binary to execute.
     */
    private $binary;

    /**
     * @var ArgumentInterface[] Array of arguments to pass with the command.
     */
    private $argumentList;


    /**
     * Constructor
     *
     * @param BinaryInterface $binary
     * @param ArgumentInterface[] $argumentList
     */
    public function __construct(BinaryInterface $binary, array $argumentList)
    {
        $this->binary = $binary;
        $this->argumentList = $argumentList;
    }

    /**
     * Execute the command and return its result.
     *
     * @return CommandResultInterface
     */
    public function run()
    {
        exec($this, $outputLines, $exitCode);

        return new ShellResult($exitCode, (array)$outputLines);
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
