<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessInterface;

/**
 * Mock environment.
 */
final class MockEnvironment implements EnvironmentInterface
{
    /**
     * Accepts a command (without arguments) and verifies whether or not it can be executed.
     *
     * @param string $command
     * @param string $cwdOverride
     *
     * @return bool
     */
    public function validateCommand($command, $cwdOverride = '')
    {
        return true;
    }

    /**
     * Accepts a command and a polling timeout and returns an object implementing RunningProcessInterface.
     *
     * @param CommandInterface $command
     * @param string $cwd
     * @param ProcessObserverInterface $processObserver
     * @param int $timeout
     * @param int $pollTimeout
     *
     * @return ProcessInterface
     */
    public function buildProcess(
        CommandInterface $command,
        $cwd,
        ProcessObserverInterface $processObserver,
        $timeout = -1,
        $pollTimeout = 1000
    ) {
        // TODO: Implement buildProcess() method - should return MockRunningProcess?
    }

    /**
     * Send the specified signal to the process.
     *
     * @param resource $process
     * @param string $signal One of RunningProcessInterface SIG* constants
     */
    public function sendSignal($process, $signal)
    {
        // Do Nothing
    }

    /**
     * Returns an array of OS strings that the environment supports.
     *
     * These strings map to values found in the PHP_OS constant.
     *
     * @return string[]
     */
    public function getSupportedList()
    {
        // Support just about anything unixy
        return [
            'CYGWIN_NT-5.1',
            'Darwin',
            'FreeBSD',
            'HP-UX',
            'IRIX64',
            'Linux',
            'NetBSD',
            'OpenBSD',
            'SunOS',
            'Unix'
        ];
    }

    /**
     * Escape an argument to be passed to the shell.
     *
     * @param string $arg
     *
     * @return string
     */
    public function escapeShellArg($arg)
    {
        // Simply wrap in single quotes
        return "'" . $arg . "'";
    }

    /**
     * Performs an platform-specific path expansions (e.g. home folder).
     *
     * @param string $path
     *
     * @return string
     */
    public function expandPath($path)
    {
        return $path;
    }

    public function applyEnvironmentVariables($command, array $envVariableList)
    {
        $envPrefix = '';
        foreach ($envVariableList as $variable => $value) {
            $envPrefix .= $variable . '=\'' . $value . '\'' . ' ';
        }

        return $envPrefix . $command;
    }
}
