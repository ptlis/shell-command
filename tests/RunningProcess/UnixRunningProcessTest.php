<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\RunningProcess;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Logger\SignalSentLogger;
use ptlis\ShellCommand\Test\Logger\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixRunningProcess;

class UnixRunningProcessTest extends ptlisShellCommandTestcase
{
    public function testRunProcess()
    {
        $command = './tests/data/test_binary';

        $process = new UnixRunningProcess($command, getcwd());
        $process->wait();

        $this->assertEquals(
            false,
            $process->isRunning()
        );

        $this->assertEquals(
            './tests/data/test_binary',
            $process->getCommand()
        );
    }

    public function testWaitWithClosure()
    {
        $command = './tests/data/test_binary';

        $process = new UnixRunningProcess($command, getcwd());
        $process->wait(function($stdOut, $stdErr) {

        });

        $this->assertEquals(
            false,
            $process->isRunning()
        );
    }

    public function testHandleCommandError()
    {
        $command = './tests/data/error_binary';

        $process = new UnixRunningProcess($command, getcwd());

        $fullStdOut = '';
        $fullStdErr = '';
        $process->wait(function($stdOut, $stdErr) use (&$fullStdOut, &$fullStdErr) {
            $fullStdOut .= $stdOut;
            $fullStdErr .= $stdErr;
        });

        $this->assertEquals(
            5,
            $process->getExitCode()
        );

        $this->assertEquals(
            'Fatal Error' . PHP_EOL,
            $fullStdErr
        );
    }

    public function testErrorGetExitCodeWhileRunning()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Cannot get exit code of still-running process.'
        );

        $command = './tests/data/sleep_binary';

        $process = new UnixRunningProcess($command, getcwd());
        $process->getExitCode();
    }

    public function testGetPid()
    {
        $command = './tests/data/sleep_binary';

        $process = new UnixRunningProcess($command, getcwd());

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

        $process = new UnixRunningProcess($command, getcwd());
        $process->wait();

        $process->getPid();
    }

    public function testStopRunning()
    {
        $command = './tests/data/sleep_binary';

        $logger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            $command,
            getcwd(),
            -1,
            1000,
            new SignalSentLogger($logger)
        );

        $process->stop();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => array(
                        'signal' => '15'
                    )
                )
            ),
            $logger->getLogs()
        );
    }

    public function testStopLongRunning()
    {
        $command = './tests/data/long_sleep_binary';

        $process = new UnixRunningProcess($command, getcwd());

        $process->stop();
    }
}
