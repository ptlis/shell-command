<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Integration\Logger;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Logger\ErrorLogger;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\Process;

/**
 * @covers \ptlis\ShellCommand\Logger\ErrorLogger
 */
class ErrorLoggerTest extends PtlisShellCommandTestcase
{
    public function testCalled(): void
    {
        $command = './tests/commands/unix/error_binary';

        $mockLogger = new MockPsrLogger(54321);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            (new UnixEnvironment())->getNormalizedCwd(),
            [],
            -1,
            1000,
            new ErrorLogger(
                $mockLogger
            )
        );
        $process->wait();

        $this->assertHasLogs(
            [
                [
                    'level' => LogLevel::ERROR,
                    'message' => 'Process exited',
                    'context' => [
                        'pid' => 54321,
                        'exit_code' => 5
                    ]
                ],
                [
                    'level' => LogLevel::ERROR,
                    'message' => 'Read from stderr',
                    'context' => [
                        'pid' => 54321,
                        'stderr' => 'Fatal Error' . PHP_EOL
                    ]
                ]
            ],
            $mockLogger->getLogs()
        );
    }

    public function testCalledWithCustomLogLevel(): void
    {
        $command = './tests/commands/unix/error_binary';

        $mockLogger = new MockPsrLogger(3344);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            (new UnixEnvironment())->getNormalizedCwd(),
            [],
            -1,
            1000,
            new ErrorLogger(
                $mockLogger,
                LogLevel::CRITICAL
            )
        );
        $process->wait();

        $this->assertHasLogs(
            [
                [
                    'level' => LogLevel::CRITICAL,
                    'message' => 'Read from stderr',
                    'context' => [
                        'pid' => 3344,
                        'stderr' => 'Fatal Error' . PHP_EOL
                    ]
                ],
                [
                    'level' => LogLevel::CRITICAL,
                    'message' => 'Process exited',
                    'context' => [
                        'pid' => 3344,
                        'exit_code' => 5
                    ]
                ]
            ],
            $mockLogger->getLogs()
        );
    }

    public function testSendSignal(): void
    {
        $command = './tests/commands/unix/sleep_binary 0.1';

        $mockLogger = new MockPsrLogger(1111);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            (new UnixEnvironment())->getNormalizedCwd(),
            [],
            -1,
            1000,
            new ErrorLogger(
                $mockLogger
            )
        );
        $process->stop();

        $this->assertHasLogs(
            [
                [
                    'level' => LogLevel::ERROR,
                    'message' => 'Process exited',
                    'context' => [
                        'pid' => 1111,
                        'exit_code' => -1
                    ]
                ]
            ],
            $mockLogger->getLogs()
        );
    }
}
