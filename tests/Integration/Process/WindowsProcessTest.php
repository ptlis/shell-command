<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration\Process;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Process;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\WindowsEnvironment;

class WindowsProcessTest extends ptlisShellCommandTestcase
{
    public function testRunProcess()
    {
        $this->skipIfNotWindows();

        $command = 'tests\\commands\\windows\\test.bat';

        $process = new Process(new WindowsEnvironment(), $command, getcwd());
        $process->wait();

        $this->assertEquals(
            false,
            $process->isRunning()
        );

        $this->assertEquals(
            'tests\\commands\\windows\\test.bat',
            $process->getCommand()
        );
    }

    public function testWaitWithClosure()
    {
        $this->skipIfNotWindows();

        $command = 'tests\\commands\\windows\\test.bat';

        $process = new Process(new WindowsEnvironment(), $command, getcwd());
        $process->wait(function($stdOut, $stdErr) {

        });

        $this->assertEquals(
            false,
            $process->isRunning()
        );
    }

    public function testHandleCommandError()
    {
        $this->skipIfNotWindows();

        $command = 'tests\\commands\\windows\\error.bat';

        $process = new Process(new WindowsEnvironment(), $command, getcwd());

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
            'Fatal Error ' . PHP_EOL,
            $fullStdErr
        );
    }

    public function testErrorGetExitCodeWhileRunning()
    {
        $this->skipIfNotWindows();

        $this->setExpectedException(
            'RuntimeException',
            'Cannot get exit code of still-running process.'
        );

        $command = 'tests\\commands\\windows\\sleep.bat';

        $process = new Process(new WindowsEnvironment(), $command, getcwd());
        $process->getExitCode();
    }

    public function testGetPid()
    {
        $this->skipIfNotWindows();

        $command = 'tests\\commands\\windows\\sleep.bat';

        $process = new Process(new WindowsEnvironment(), $command, getcwd());

        $this->assertNotNull(
            $process->getPid()
        );
    }

    public function testErrorGetPidNotRunning()
    {
        $this->skipIfNotWindows();

        $this->setExpectedException(
            'RuntimeException',
            'Cannot get the process id of a process that has already exited.'
        );

        $command = 'tests\\commands\\windows\\test.bat';

        $process = new Process(new WindowsEnvironment(), $command, getcwd());
        $process->wait();

        $process->getPid();
    }

//    public function testStopRunning()
//    {
//        $this->skipIfNotWindows();
//
//        $command = 'tests\\commands\\windows\\sleep.bat';
//
//        $logger = new MockPsrLogger();
//
//        $process = new Process(
//            new WindowsEnvironment(),
//            $command,
//            getcwd(),
//            -1,
//            1000,
//            new AllLogger($logger)
//        );
//
//        $process->stop();
//
//        $this->assertLogsMatch(
//            array(
//                array(
//                    'level' => LogLevel::DEBUG,
//                    'message' => 'Process created',
//                    'context' => array(
//                        'command' => 'tests\\commands\\windows\\sleep.bat'
//                    )
//                ),
//                array(
//                    'level' => LogLevel::DEBUG,
//                    'message' => 'Signal sent',
//                    'context' => array(
//                        'signal' => ProcessInterface::SIGTERM
//                    )
//                ),
//                array(
//                    'level' => LogLevel::DEBUG,
//                    'message' => 'Process exited',
//                    'context' => array(
//                        'exit_code' => -1
//                    )
//                )
//            ),
//            $logger->getLogs()
//        );
//    }
//
//    public function testTimeoutLongRunning()
//    {
//        $this->skipIfNotWindows();
//
//        $command = './tests/commands/unix/long_sleep_binary';
//
//        $logger = new MockPsrLogger();
//
//        $process = new Process(
//            new UnixEnvironment(),
//            $command,
//            getcwd(),
//            500000,
//            1000,
//            new AllLogger($logger)
//        );
//
//        $process->wait();
//
//        $this->assertLogsMatch(
//            array(
//                array(
//                    'level' => LogLevel::DEBUG,
//                    'message' => 'Process created',
//                    'context' => array(
//                        'command' => './tests/commands/unix/long_sleep_binary'
//                    )
//                ),
//                array(
//                    'level' => LogLevel::DEBUG,
//                    'message' => 'Signal sent',
//                    'context' => array(
//                        'signal' => ProcessInterface::SIGTERM
//                    )
//                ),
//                array(
//                    'level' => LogLevel::DEBUG,
//                    'message' => 'Process exited',
//                    'context' => array(
//                        'exit_code' => -1
//                    )
//                )
//            ),
//            $logger->getLogs()
//        );
//    }
//
//    public function testSendInvalidSignal()
//    {
//        $this->skipIfNotWindows();
//
//        $this->setExpectedException(
//            '\ptlis\ShellCommand\Exceptions\CommandExecutionException',
//            'Unknown signal "wibble" provided'
//        );
//
//        $command = './tests/commands/unix/long_sleep_binary';
//
//        $process = new Process(
//            new UnixEnvironment(),
//            $command,
//            getcwd(),
//            500000,
//            1000,
//            new NullProcessObserver()
//        );
//
//        $process->sendSignal('wibble');
//    }
}
