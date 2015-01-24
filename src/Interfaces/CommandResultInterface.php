<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Interfaces;

/**
 * Interface class that command results must implement.
 */
interface CommandResultInterface
{
    /**
     * Get the string representation of the contents of stdout when the command was executed.
     *
     * @return string
     */
    public function getStdOut();

    /**
     * Get the array representation of the contents of stdout when the command was executed (each element of the array
     * contains one line from the output)..
     *
     * @return string[]
     */
    public function getStdOutLines();

    /**
     * Get the string representation of the contents of stderr when the command was executed.
     *
     * @return string
     */
    public function getStdErr();

    /**
     * Get the array representation of the contents of stderr when the command was executed (each element of the array
     * contains one line from the output)..
     *
     * @return string[]
     */
    public function getStdErrLines();

    /**
     * Get the exit code from the executed command.
     *
     * @return int
     */
    public function getExitCode();
}
