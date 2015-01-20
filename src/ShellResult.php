<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandResultInterface;

/**
 * Class that stores the result of executing a shell command.
 */
class ShellResult implements CommandResultInterface
{
    /**
     * @var int The exit code emitted by the command.
     */
    private $exitCode;

    /**
     * @var string[] The output of the command.
     */
    private $outputLines;


    /**
     * Constructor.
     *
     * @param int $exitCode
     * @param string[] $outputLines
     */
    public function __construct($exitCode, array $outputLines)
    {
        $this->exitCode = $exitCode;
        $this->outputLines = $outputLines;
    }

    /**
     * Get the lines output by the executed command.
     *
     * @return string[]
     */
    public function getOutput()
    {
        return $this->outputLines;
    }

    /**
     * Get the exit code from the executed command.
     *
     * @return int
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
