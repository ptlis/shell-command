<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\RunningProcess;

use ptlis\ShellCommand\UnixRunningProcess;

class UnixRunningProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testRunProcess()
    {
        $command = './tests/data/test_binary';

        $process = new UnixRunningProcess($command);
        $process->wait();

        $this->assertEquals(
            false,
            $process->isRunning()
        );
    }

    public function testHandleCommandError()
    {
        $command = './tests/data/error_binary';

        $process = new UnixRunningProcess($command);
        $process->wait();

        $this->assertEquals(
            5,
            $process->getExitCode()
        );

        $this->assertEquals(
            'Fatal Error' . PHP_EOL,
            $process->readOutput(UnixRunningProcess::STDERR)
        );
    }

    public function testErrorGetExitCodeWhileRunning()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Cannot get exit code of still-running process.'
        );

        $command = './tests/data/sleep_binary';

        $process = new UnixRunningProcess($command);
        $process->getExitCode();
    }

    public function testGetPid()
    {
        $command = './tests/data/sleep_binary';

        $process = new UnixRunningProcess($command);

        $this->assertNotNull(
            $process->getPid()
        );
    }

    public function testErrorGetPidNotRunning()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Cannot get the process id of a process that has already exited.'
        );

        $command = './tests/data/test_binary';

        $process = new UnixRunningProcess($command);
        $process->wait();

        $process->getPid();
    }
}
