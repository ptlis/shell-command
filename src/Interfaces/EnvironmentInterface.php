<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Interfaces;

/**
 * Interface class that operating-system specific classes must implement.
 */
interface EnvironmentInterface
{
    /**
     * Accepts a command (without arguments) and verifies whether or not it can be executed.
     *
     * @param string $command
     * @param string $cwdOverride
     *
     * @return bool
     */
    public function validateCommand($command, $cwdOverride = '');

    /**
     * Send the specified signal to the process.
     *
     * @param resource $process
     * @param string $signal One of ProcessInterface SIG* constants
     */
    public function sendSignal($process, $signal);

    /**
     * Returns an array of OS strings that the environment supports.
     *
     * These strings map to values found in the PHP_OS constant.
     *
     * @return string[]
     */
    public function getSupportedList();

    /**
     * Escape an argument to be passed to the shell.
     *
     * @param string $arg
     *
     * @return string
     */
    public function escapeShellArg($arg);

    /**
     * Performs an platform-specific path expansions (e.g. home folder).
     *
     * @param string $path
     *
     * @return string
     */
    public function expandPath($path);
}
