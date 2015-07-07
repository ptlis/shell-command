<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration\RunningProcess {

    use ptlis\ShellCommand\Interfaces\RunningProcessInterface;
    use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
    use ptlis\ShellCommand\UnixEnvironment;
    use ptlis\ShellCommand\UnixRunningProcess;

    class UnixRunningProcessStopFailureTest extends ptlisShellCommandTestcase
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
                'Call to proc_terminate with signal "' . RunningProcessInterface::SIGTERM . '" failed for unknown reason.'
            );

            $command = './tests/commands/unix/test_binary';

            $process = new UnixRunningProcess(new UnixEnvironment(), $command, getcwd());
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
