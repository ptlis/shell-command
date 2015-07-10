<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration\Process {

    use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
    use ptlis\ShellCommand\UnixEnvironment;
    use ptlis\ShellCommand\Process;

    class UnixProcessStartFailureTest extends ptlisShellCommandTestcase
    {
        public function setUp()
        {
            global $mockProcOpenFail;
            $mockProcOpenFail = true;
        }

        public function tearDown()
        {
            global $mockProcOpenFail;
            $mockProcOpenFail = false;
        }

        public function testProcOpenFail()
        {
            $this->skipIfNotUnix();

            $this->setExpectedException(
                'ptlis\ShellCommand\Exceptions\CommandExecutionException',
                'Call to proc_open failed for unknown reason.'
            );

            $command = './tests/commands/unix/test_binary';

            $process = new Process(new UnixEnvironment(), $command, getcwd());
        }
    }
}

/**
 * Mock the proc_open command failing.
 */
namespace ptlis\ShellCommand {
    $mockProcOpenFail = false;

    function proc_open($cmd, array $descriptorspec, array &$pipes, $cwd = null, array $env = null, array $other_options = array()) {
        global $mockProcOpenFail;

        if ($mockProcOpenFail) {
            return false;

        } else {
            return \proc_open($cmd, $descriptorspec, $pipes, $cwd, $env, $other_options);
        }
    }
}
