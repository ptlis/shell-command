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
     * @var string The contents of stdout when executing the command.
     */
    private $stdOut;

    /**
     * @var string The contents of stderr when executing the command.
     */
    private $stdErr;


    /**
     * Constructor.
     *
     * @param int $exitCode
     * @param string $stdOut
     * @param string $stdErr
     */
    public function __construct($exitCode, $stdOut, $stdErr)
    {
        $this->exitCode = $exitCode;
        $this->stdOut = $stdOut;
        $this->stdErr = $stdErr;
    }

    /**
     * Get the contents of stdout when executing the command.
     *
     * @return string
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }

    /**
     * Get the array representation of the contents of stdout when the command was executed (each element of the array
     * contains one line from the output)..
     *
     * @return string[]
     */
    public function getStdOutLines()
    {
        return $this->stringToArray($this->stdOut);
    }

    /**
     * Get the contents of stderr when executing the command.
     *
     * @return string
     */
    public function getStdErr()
    {
        return $this->stdErr;
    }

    /**
     * Get the array representation of the contents of stderr when the command was executed (each element of the array
     * contains one line from the output)..
     *
     * @return string[]
     */
    public function getStdErrLines()
    {
        return $this->stringToArray($this->stdErr);
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

    /**
     * Accepts console output as a string and returns an array of it split by newlines.
     *
     * @param string $string
     * @return string[]
     */
    private function stringToArray($string)
    {
        $lines = preg_split('/\R/', $string);
        if (1 === count($lines) && '' === $lines[0]) {
            $lines = array();
        }

        return $lines;
    }
}
