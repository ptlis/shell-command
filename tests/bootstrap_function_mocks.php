<?php

/**
 * We're abusing the fact that redeclaring global functions in a namespace will override those functions when called
 * without being fully-qualified.
 */
namespace ptlis\ShellCommand {

    /**
     * Mock proc_open failing.
     */
    $mockProcOpenFail = false;
    function proc_open($cmd, array $descriptorspec, array &$pipes, $cwd = null, array $env = null, array $other_options = []) {
        global $mockProcOpenFail;

        if ($mockProcOpenFail) {
            return false;

        } else {
            return \proc_open($cmd, $descriptorspec, $pipes, $cwd, $env, $other_options);
        }
    }


    /**
     * Mock proc_terminate failing.
     */
    $mockProcTerminateFail = false;
    function proc_terminate($signal) {
        global $mockProcTerminateFail;

        if ($mockProcTerminateFail) {
            return false;

        } else {
            return \proc_terminate($signal);
        }
    }


    /**
     * Mock the is_executable to allow faked directories to appear real.
     */
    $mockIsExecutable = false;
    function is_executable($path) {
        global $mockIsExecutable;

        if ($mockIsExecutable) {
            return true;

        } else {
            return \is_executable($path);
        }
    }
}
