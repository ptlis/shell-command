<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Interfaces;

/**
 * Interface class that commands must implement.
 */
interface CommandInterface
{
    /**
     * Run the command blocking further execution, on completion return a result.
     */
    public function runSynchronous(): ProcessOutputInterface;

    /**
     * Run the command asynchronously, return an object representing the process.
     */
    public function runAsynchronous(): ProcessInterface;

    /**
     * Gets the string representation of the command.
     */
    public function __toString(): string;
}
