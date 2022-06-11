<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Interfaces;

/**
 * Interface class that operating-system specific classes must implement.
 */
interface EnvironmentInterface
{
    /**
     * Accepts a command (without arguments) and verifies that it can be executed.
     */
    public function validateCommand(string $command, string $cwdOverride = ''): bool;

    /**
     * Send the specified signal to the process.
     *
     * @param resource $process
     * @param string $signal One of ProcessInterface SIG* constants
     */
    public function sendSignal($process, string $signal): void;

    /**
     * Returns an array of OS strings that the environment supports.
     *
     * These strings map to the values that can be set in the PHP_OS constant.
     *
     * @return array<string>
     */
    public function getSupportedList(): array;

    /**
     * Escape an argument to be passed to the shell.
     */
    public function escapeShellArg(string $arg): string;

    /**
     * Performs a platform-specific path expansions (e.g. of the home folder).
     */
    public function expandPath(string $path): string;

    /**
     * Get normalized CWD - if Override is set return that otherwise return the real CWD.
     */
    public function getNormalizedCwd(string $cwdOverride = ''): string;
}
