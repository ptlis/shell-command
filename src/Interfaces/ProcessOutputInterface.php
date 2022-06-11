<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Interfaces;

/**
 * Interface for classes storing the output of a terminated process.
 */
interface ProcessOutputInterface
{
    /**
     * Get the string representation of the contents of stdout when the command was executed.
     */
    public function getStdOut(): string;

    /**
     * Get the array representation of the contents of stdout when the command was executed (each element of the array
     * contains one line from the output)..
     *
     * @return array<string>
     */
    public function getStdOutLines(): array;

    /**
     * Get the string representation of the contents of stderr when the command was executed.
     */
    public function getStdErr(): string;

    /**
     * Get the array representation of the contents of stderr when the command was executed (each element of the array
     * contains one line from the output)..
     *
     * @return array<string>
     */
    public function getStdErrLines(): array;

    /**
     * Get the exit code from the executed command.
     */
    public function getExitCode(): int;

    /**
     * Get the command that was executed as a string (e.g. "ls -lah").
     *
     * This is here purely to make debugging commands easier.
     */
    public function getExecutedCommand(): string;

    /**
     * Get the working directory that command was executed in.
     *
     * This is here purely to make debugging commands easier.
     */
    public function getWorkingDirectory(): string;
}
