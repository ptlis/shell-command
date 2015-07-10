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
 * Interface class that commands must implement.
 */
interface CommandInterface
{
    /**
     * Run the command blocking further execution, on completion return a result
     *
     * @return ProcessOutputInterface
     */
    public function runSynchronous();

    /**
     * Run the command asynchronously, return an object representing the process.
     *
     * @return ProcessInterface
     */
    public function runAsynchronous();

    /**
     * Gets the string representation of the command.
     *
     * @return string
     */
    public function __toString();
}
