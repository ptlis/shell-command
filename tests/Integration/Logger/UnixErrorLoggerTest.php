<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Test\Integration\Logger;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Logger\ErrorLogger;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\Process;

class UnixErrorLoggerTest extends ptlisShellCommandTestcase
{
    public function testCalled()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/error_binary';

        $mockLogger = new MockPsrLogger();

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new ErrorLogger(
                $mockLogger
            )
        );
        $process->wait();

        $this->assertLogsMatch(
            [
                [
                    'level' => LogLevel::ERROR,
                    'message' => 'Process exited',
                    'context' => [
                        'exit_code' => 5
                    ]
                ],
                [
                    'level' => LogLevel::ERROR,
                    'message' => 'Read from stderr',
                    'context' => [
                        'stderr' => 'Fatal Error' . PHP_EOL
                    ]
                ]
            ],
            $mockLogger->getLogs()
        );
    }

    public function testCalledWithCustomLogLevel()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/error_binary';

        $mockLogger = new MockPsrLogger();

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new ErrorLogger(
                $mockLogger,
                LogLevel::CRITICAL
            )
        );
        $process->wait();

        $this->assertLogsMatch(
            [
                [
                    'level' => LogLevel::CRITICAL,
                    'message' => 'Read from stderr',
                    'context' => [
                        'stderr' => 'Fatal Error' . PHP_EOL
                    ]
                ],
                [
                    'level' => LogLevel::CRITICAL,
                    'message' => 'Process exited',
                    'context' => [
                        'exit_code' => 5
                    ]
                ]
            ],
            $mockLogger->getLogs()
        );
    }

    public function testSendSignal()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/sleep_binary';

        $mockLogger = new MockPsrLogger();

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new ErrorLogger(
                $mockLogger
            )
        );
        $process->stop();

        $this->assertLogsMatch(
            [
                [
                    'level' => LogLevel::ERROR,
                    'message' => 'Process exited',
                    'context' => [
                        'exit_code' => -1
                    ]
                ]
            ],
            $mockLogger->getLogs()
        );
    }
}
