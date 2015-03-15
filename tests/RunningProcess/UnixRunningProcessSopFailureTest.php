<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\RunningProcess {

    use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
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
            $this->setExpectedException(
                'ptlis\ShellCommand\Exceptions\CommandExecutionException',
                'Call to proc_terminate with signal "' . SIGTERM . '" failed for unknown reason.'
            );

            $command = './tests/data/test_binary';

            $process = new UnixRunningProcess($command, getcwd());
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
