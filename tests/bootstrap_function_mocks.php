<?php

/**
 * We're abusing the fact that redeclaring global functions in a namespace will override those functions when called
 * without being fully-qualified.
 */

declare(strict_types=1);

namespace ptlis\ShellCommand {
    /**
     * Mock proc_open failing.
     */
    $mockProcOpen = false;
    $mockProcOpenRetval = false;
    /**
     * @param string $cmd
     * @param array<mixed> $descriptorspec
     * @param array<int, resource> $pipes
     * @param string $cwd
     * @param array<string, string>|null $env
     * @param array<mixed> $other_options
     * @return false|resource
     */
    function proc_open(
        $cmd,
        array $descriptorspec,
        array &$pipes,
        $cwd = null,
        array $env = null,
        array $other_options = []
    ) {
        global $mockProcOpen;
        global $mockProcOpenRetval;

        if ($mockProcOpen) {
            return $mockProcOpenRetval;
        } else {
            return \proc_open($cmd, $descriptorspec, $pipes, $cwd, $env, $other_options);
        }
    }


    /**
     * Mock proc_terminate failing.
     */
    $mockProcTerminate = false;
    $mockProcTerminateRetval = false;
    $mockProcTerminateCalled = false;
    /**
     * @param resource $process
     * @param int $signal
     * @return bool
     */
    function proc_terminate($process, $signal)
    {
        global $mockProcTerminate;
        global $mockProcTerminateRetval;
        global $mockProcTerminateCalled;

        if ($mockProcTerminate) {
            // Indicate that this has been called
            $mockProcTerminateCalled = true;

            return $mockProcTerminateRetval;
        } else {
            return \proc_terminate($process, $signal);
        }
    }


    /**
     * Mock the is_executable to allow faked directories to appear real.
     */
    $mockIsExecutable = false;
    $mockIsExecutableRetval = true;
    /**
     * @param string $path
     * @return bool
     */
    function is_executable($path)
    {
        global $mockIsExecutable;
        global $mockIsExecutableRetval;

        if ($mockIsExecutable) {
            return $mockIsExecutableRetval;
        } else {
            return \is_executable($path);
        }
    }
}
