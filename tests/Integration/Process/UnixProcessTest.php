<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration\Process;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Logger\SignalSentLogger;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\Process;

class UnixProcessTest extends ptlisShellCommandTestcase
{
    public function testRunProcess()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/test_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());
        $process->wait();

        $this->assertEquals(
            false,
            $process->isRunning()
        );

        $this->assertEquals(
            './tests/commands/unix/test_binary',
            $process->getCommand()
        );
    }

    public function testWaitWithClosure()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/test_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());
        $process->wait(function($stdOut, $stdErr) {

        });

        $this->assertEquals(
            false,
            $process->isRunning()
        );
    }

    public function testHandleCommandError()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/error_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());

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
        $this->skipIfNotUnix();

        $this->setExpectedException(
            'RuntimeException',
            'Cannot get exit code of still-running process.'
        );

        $command = './tests/commands/unix/sleep_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());
        $process->getExitCode();
    }

    public function testGetPid()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/sleep_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());

        $this->assertNotNull(
            $process->getPid()
        );
    }

    public function testErrorGetPidNotRunning()
    {
        $this->skipIfNotUnix();

        $this->setExpectedException(
            'RuntimeException',
            'Cannot get the process id of a process that has already exited.'
        );

        $command = './tests/commands/unix/test_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());
        $process->wait();

        $process->getPid();
    }

    public function testStopRunning()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/sleep_binary';

        $logger = new MockPsrLogger();

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new AllLogger($logger)
        );

        $process->stop();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => array(
                        'command' => './tests/commands/unix/sleep_binary'
                    )
                ),
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => array(
                        'signal' => ProcessInterface::SIGTERM
                    )
                ),
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => array(
                        'exit_code' => -1
                    )
                )
            ),
            $logger->getLogs()
        );
    }

    public function testTimeoutLongRunning()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/long_sleep_binary';

        $logger = new MockPsrLogger();

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            500000,
            1000,
            new AllLogger($logger)
        );

        $process->wait();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => array(
                        'command' => './tests/commands/unix/long_sleep_binary'
                    )
                ),
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => array(
                        'signal' => ProcessInterface::SIGTERM
                    )
                ),
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => array(
                        'exit_code' => -1
                    )
                )
            ),
            $logger->getLogs()
        );
    }

    public function testSendInvalidSignal()
    {
        $this->skipIfNotUnix();

        $this->setExpectedException(
            '\ptlis\ShellCommand\Exceptions\CommandExecutionException',
            'Unknown signal "wibble" provided'
        );

        $command = './tests/commands/unix/long_sleep_binary';

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            500000,
            1000,
            new NullProcessObserver()
        );

        $process->sendSignal('wibble');
    }
}
