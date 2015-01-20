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
 * Interface class that shell commands must implement.
 */
interface CommandInterface
{
    /**
     * Execute the command and return its result.
     *
     * @return CommandResultInterface
     */
    public function run();

    /**
     * Gets the string representation of the command, ready to execute.
     *
     * @return string
     */
    public function __toString();
}
