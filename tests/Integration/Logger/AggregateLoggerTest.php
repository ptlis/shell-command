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
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Logger\AggregateLogger;
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Process;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\Logger\AggregateLogger
 */
class AggregateLoggerTest extends PtlisShellCommandTestcase
{
    public function testAggregateLogger(): void
    {
        $command = './tests/commands/unix/sleep_binary 0.1';

        $mockLogger = new MockPsrLogger(1234);
        $allLogger = new AllLogger($mockLogger);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            (new UnixEnvironment())->getNormalizedCwd(),
            [],
            -1,
            1000,
            new AggregateLogger([
                $allLogger,
                new NullProcessObserver()
            ])
        );

        $process->sendSignal(ProcessInterface::SIGTERM);
        $process->wait();

        $this->assertHasLogs(
            [
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => [
                        'pid' => 1234,
                        'command' => './tests/commands/unix/sleep_binary 0.1',
                        'cwd' => (new UnixEnvironment())->getNormalizedCwd(),
                        'env_vars' => []
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => [
                        'pid' => 1234,
                        'signal' => 'SIGTERM'
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
            $mockLogger->getLogs()
        );
    }
}
