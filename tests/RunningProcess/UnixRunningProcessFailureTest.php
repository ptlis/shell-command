<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\RunningProcess {

    use ptlis\ShellCommand\UnixRunningProcess;

    class UnixRunningProcessFailureTest extends \PHPUnit_Framework_TestCase
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
            $this->setExpectedException(
                'ptlis\ShellCommand\Exceptions\CommandExecutionException',
                'Call to proc_open failed for unknown reason.'
            );

            $command = './tests/data/test_binary';

            $process = new UnixRunningProcess($command);
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
