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
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\Process;

/**
 * @covers \ptlis\ShellCommand\Logger\AllLogger
 */
class AllLoggerTest extends PtlisShellCommandTestcase
{
    public function testCalled(): void
    {
        $command = './tests/commands/unix/test_binary';

        $mockLogger = new MockPsrLogger(4523);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            (new UnixEnvironment())->getNormalizedCwd(),
            [],
            -1,
            1000,
            new AllLogger(
                $mockLogger
            )
        );
        $process->wait();

        $this->assertHasLogs(
            [
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => [
                        'pid' => 4523,
                        'command' => './tests/commands/unix/test_binary',
                        'cwd' => (new UnixEnvironment())->getNormalizedCwd(),
                        'env_vars' => []
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Read from stdout',
                    'context' => [
                        'pid' => 4523,
                        'stdout' => 'Test command' . PHP_EOL . PHP_EOL
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => [
                        'pid' => 4523,
                        'exit_code' => 0
                    ]
                ]
            ],
            $mockLogger->getLogs()
        );
    }

    public function testSendSignal(): void
    {
        $command = './tests/commands/unix/sleep_binary 0.1';

        $mockLogger = new MockPsrLogger(9241);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            (new UnixEnvironment())->getNormalizedCwd(),
            [],
            -1,
            1000,
            new AllLogger(
                $mockLogger
            )
        );
        $process->stop();

        $this->assertHasLogs(
            [
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => [
                        'pid' => 9241,
                        'command' => './tests/commands/unix/sleep_binary 0.1',
                        'cwd' => (new UnixEnvironment())->getNormalizedCwd(),
                        'env_vars' => []
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => [
                        'pid' => 9241,
                        'signal' => 'SIGTERM'
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => [
                        'pid' => 9241,
                        'exit_code' => -1
                    ]
                ]
            ],
            $mockLogger->getLogs()
        );
    }
}
