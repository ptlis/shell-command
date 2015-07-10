<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration\Process {

    use ptlis\ShellCommand\Interfaces\ProcessInterface;
    use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
    use ptlis\ShellCommand\UnixEnvironment;
    use ptlis\ShellCommand\Process;

    class UnixProcessStopFailureTest extends ptlisShellCommandTestcase
    {
        public function setUp()
        {
            global $mockProcTerminateFail;
            $mockProcTerminateFail = true;
        }

        public function tearDown()
        {
            global $mockProcTerminateFail;
            $mockProcTerminateFail = false;
        }

        public function testProcOpenFail()
        {
            $this->skipIfNotUnix();

            $this->setExpectedException(
                'ptlis\ShellCommand\Exceptions\CommandExecutionException',
                'Call to proc_terminate with signal "' . ProcessInterface::SIGTERM . '" failed for unknown reason.'
            );

            $command = './tests/commands/unix/test_binary';

            $process = new Process(new UnixEnvironment(), $command, getcwd());
            $process->stop();
        }
    }
}

/**
 * Mock the proc_terminate command failing.
 */
namespace ptlis\ShellCommand {
    $mockProcTerminateFail = false;

    function proc_terminate($signal) {
        global $mockProcTerminateFail;

        if ($mockProcTerminateFail) {
            return false;

        } else {
            return \proc_terminate($signal);
        }
    }
}
