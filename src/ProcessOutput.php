<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Stores the output of a terminated process.
 */
class ProcessOutput implements ProcessOutputInterface
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
     * @inheritDoc
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }

    /**
     * @inheritDoc
     */
    public function getStdOutLines()
    {
        return $this->stringToArray($this->stdOut);
    }

    /**
     * @inheritDoc
     */
    public function getStdErr()
    {
        return $this->stdErr;
    }

    /**
     * @inheritDoc
     */
    public function getStdErrLines()
    {
        return $this->stringToArray($this->stdErr);
    }

    /**
     * @inheritDoc
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
