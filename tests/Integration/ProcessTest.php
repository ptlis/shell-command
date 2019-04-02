<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Exceptions\CommandExecutionException;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\Process;

/**
 * @covers \ptlis\ShellCommand\Process
 */
class ProcessTest extends ptlisShellCommandTestcase
{
    public function tearDown()
    {
        global $mockProcOpen;
        $mockProcOpen = false;

        global $mockProcTerminate;
        global $mockProcTerminateRetval;
        $mockProcTerminate = false;
        $mockProcTerminateRetval = false;
    }

    public function testRunProcess()
    {
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
        $command = './tests/commands/unix/error_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());

        $fullStdOut = '';
        $fullStdErr = '';
        $output = $process->wait(function($stdOut, $stdErr) use (&$fullStdOut, &$fullStdErr) {
            $fullStdOut .= $stdOut;
            $fullStdErr .= $stdErr;
        });

        $this->assertEquals(
            5,
            $output->getExitCode()
        );

        $this->assertEquals(
            'Fatal Error' . PHP_EOL,
            $fullStdErr
        );
    }

    public function testGetPid()
    {
        $command = './tests/commands/unix/sleep_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());

        $this->assertNotNull(
            $process->getPid()
        );
    }

    public function testStopRunning()
    {
        $command = './tests/commands/unix/sleep_binary';

        $logger = new MockPsrLogger(555);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new AllLogger($logger)
        );

        $process->stop();

        $this->assertHasLogs(
            [
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => [
                        'pid' => 555,
                        'command' => './tests/commands/unix/sleep_binary'
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => [
                        'pid' => 555,
                        'signal' => ProcessInterface::SIGTERM
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => [
                        'pid' => 555,
                        'exit_code' => -1
                    ]
                ]
            ],
            $logger->getLogs()
        );
    }

    public function testStopRunningRequiresSigkill()
    {
        global $mockProcTerminate;
        $mockProcTerminate = true;

        global $mockProcTerminateRetval;
        $mockProcTerminateRetval = true;

        $command = './tests/commands/unix/sleep_binary';

        $logger = new MockPsrLogger(9999);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new AllLogger($logger)
        );

        $process->stop();

        $this->assertHasLogs(
            [
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => [
                        'pid' => 9999,
                        'command' => './tests/commands/unix/sleep_binary'
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => [
                        'pid' => 9999,
                        'signal' => ProcessInterface::SIGTERM
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => [
                        'pid' => 9999,
                        'signal' => ProcessInterface::SIGKILL
                    ]
                ],
            ],
            $logger->getLogs()
        );
    }

    public function testTimeoutLongRunning()
    {
        $command = './tests/commands/unix/long_sleep_binary';

        $logger = new MockPsrLogger(1234);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            500000,
            1000,
            new AllLogger($logger)
        );

        $process->wait();

        $this->assertHasLogs(
            [
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => [
                        'pid' => 1234,
                        'command' => './tests/commands/unix/long_sleep_binary'
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => [
                        'pid' => 1234,
                        'signal' => ProcessInterface::SIGTERM
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => [
                        'pid' => 1234,
                        'exit_code' => -1
                    ]
                ]
            ],
            $logger->getLogs()
        );
    }

    public function testSendInvalidSignal()
    {
        $this->expectException(CommandExecutionException::class);
        $this->expectExceptionMessage('Unknown signal "wibble" provided');

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

    public function testProcOpenFail()
    {
        global $mockProcOpen;
        $mockProcOpen = true;

        $this->expectException(CommandExecutionException::class);
        $this->expectExceptionMessage('Call to proc_open failed for unknown reason.');

        $command = './tests/commands/unix/test_binary';

        new Process(new UnixEnvironment(), $command, getcwd());
    }

    public function testProcTerminateFail()
    {
        global $mockProcTerminate;
        $mockProcTerminate = true;

        $this->expectException(CommandExecutionException::class);
        $this->expectExceptionMessage('Call to proc_terminate with signal "' . ProcessInterface::SIGTERM . '" failed for unknown reason.');

        $command = './tests/commands/unix/test_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());
        $process->stop();
    }

    public function testGetAllStdout()
    {
        $command = './tests/commands/unix/slow_stdout_binary';

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new NullProcessObserver()
        );

        $result = $process->wait();

        $this
            ->assertEquals(
                ['1', '2', '3', '4', '5', ''],
                $result->getStdOutLines()
            );
    }
}
