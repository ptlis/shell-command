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
 * Interface class that synchronously executed shell commands must implement.
 */
interface SynchronousCommandInterface
{
    /**
     * Execute the command and return its result.
     *
     * @return CommandResultInterface
     */
    public function run();

    /**
     * Gets the string representation of the command.
     *
     * @return string
     */
    public function __toString();
}
