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
     * Get the lines output by the executed command.
     *
     * @return string[]
     */
    public function getOutput();

    /**
     * Get the exit code from the executed command.
     *
     * @return int
     */
    public function getExitCode();
}
