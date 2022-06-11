<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Integration;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Exceptions\CommandExecutionException;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\Process;
use React\EventLoop\Factory;

/**
 * @covers \ptlis\ShellCommand\Process
 */
class ProcessTest extends PtlisShellCommandTestcase
{
    public function tearDown(): void
    {
        global $mockProcOpen;
        $mockProcOpen = false;

        global $mockProcTerminate;
        global $mockProcTerminateRetval;
        global $mockProcTerminateCalled;
        $mockProcTerminate = false;
        $mockProcTerminateRetval = false;
        $mockProcTerminateCalled = false;
    }

    public function testRunProcess(): void
    {
        $command = './tests/commands/unix/test_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd(), ['FOO' => 'bar']);
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

    public function testRunWithPromiseSuccess(): void
    {
        $command = './tests/commands/unix/test_binary';

        $eventLoop = Factory::create();

        $promise = (new Process(new UnixEnvironment(), $command, getcwd()))
            ->getPromise($eventLoop);

        $successCalled = false;
        $failureCalled = false;
        $promise->then(
            function () use (&$successCalled) {
                $successCalled = true;
            },
            function () use (&$failureCalled) {
                $failureCalled = true;
            }
        );

        $eventLoop->run();

        $this->assertTrue($successCalled);
        $this->assertFalse($failureCalled);
    }

    public function testRunWithPromiseError(): void
    {
        $command = './tests/commands/unix/error_binary';

        $eventLoop = Factory::create();

        $promise = (new Process(new UnixEnvironment(), $command, getcwd()))
            ->getPromise($eventLoop);

        $successCalled = false;
        $failureCalled = false;
        $promise->then(
            function () use (&$successCalled) {
                $successCalled = true;
            },
            function () use (&$failureCalled) {
                $failureCalled = true;
            }
        );

        $eventLoop->run();

        $this->assertFalse($successCalled);
        $this->assertTrue($failureCalled);
    }

    public function testWaitWithClosure(): void
    {
        $command = './tests/commands/unix/test_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());
        $process->wait(function ($stdOut, $stdErr) {
        });

        $this->assertEquals(
            false,
            $process->isRunning()
        );
    }

    public function testHandleCommandError(): void
    {
        $command = './tests/commands/unix/error_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());

        $fullStdOut = '';
        $fullStdErr = '';
        $output = $process->wait(function ($stdOut, $stdErr) use (&$fullStdOut, &$fullStdErr) {
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

    public function testGetPid(): void
    {
        $command = './tests/commands/unix/sleep_binary 0.1';

        $process = new Process(new UnixEnvironment(), $command, getcwd());

        $this->assertNotNull(
            $process->getPid()
        );
    }

    public function testStopRunning()
    {
        $command = './tests/commands/unix/sleep_binary 0.1';

        $logger = new MockPsrLogger(555);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            [],
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
                        'command' => './tests/commands/unix/sleep_binary 0.1',
                        'cwd' => getcwd(),
                        'env_vars' => []
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

    public function testStopRunningRequiresSigkill(): void
    {
        global $mockProcTerminate;
        $mockProcTerminate = true;

        global $mockProcTerminateRetval;
        $mockProcTerminateRetval = true;

        $command = './tests/commands/unix/sleep_binary 2';

        $logger = new MockPsrLogger(9999);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            [],
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
                        'command' => './tests/commands/unix/sleep_binary 2',
                        'cwd' => getcwd(),
                        'env_vars' => []
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

    public function testTimeoutLongRunning(): void
    {
        $command = './tests/commands/unix/sleep_binary 1';

        $logger = new MockPsrLogger(1234);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            [],
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
                        'command' => './tests/commands/unix/sleep_binary 1',
                        'cwd' => getcwd(),
                        'env_vars' => []
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

    public function testSendInvalidSignal(): void
    {
        $this->expectException(CommandExecutionException::class);
        $this->expectExceptionMessage('Unknown signal "wibble" provided');

        $command = './tests/commands/unix/sleep_binary 1';

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            [],
            500000,
            1000,
            new NullProcessObserver()
        );

        $process->sendSignal('wibble');
    }

    public function testProcOpenFail(): void
    {
        global $mockProcOpen;
        $mockProcOpen = true;

        $this->expectException(CommandExecutionException::class);
        $this->expectExceptionMessage('Call to proc_open failed for unknown reason.');

        $command = './tests/commands/unix/test_binary';

        new Process(new UnixEnvironment(), $command, getcwd());
    }

    public function testProcTerminateFail(): void
    {
        global $mockProcTerminate;
        $mockProcTerminate = true;

        $this->expectException(CommandExecutionException::class);
        $this->expectExceptionMessage('Call to proc_terminate with signal "SIGTERM" failed for unknown reason.');

        $command = './tests/commands/unix/test_binary';

        $process = new Process(new UnixEnvironment(), $command, getcwd());
        $process->stop();
    }

    public function testGetAllStdout(): void
    {
        $command = './tests/commands/unix/slow_stdout_binary';

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            [],
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

    public function testWriteInput(): void
    {
        $command = './tests/commands/unix/echo_cmd';

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            [],
            -1,
            1000,
            new NullProcessObserver()
        );

        $process->writeInput('Hello world');
        usleep(500000); // the bash script needs some time to respond, half second is fair
        $output = $process->readOutput(ProcessInterface::STDOUT);

        $this
            ->assertEquals(
                "Hello world\n",
                $output
            );
    }
}
