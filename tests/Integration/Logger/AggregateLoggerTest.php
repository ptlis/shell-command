<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Test\Integration\Logger;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Logger\AggregateLogger;
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Process;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\Logger\AggregateLogger
 */
class AggregateLoggerTest extends ptlisShellCommandTestcase
{
    public function testAggregateLogger()
    {
        $command = './tests/commands/unix/long_sleep_binary';

        $mockLogger = new MockPsrLogger(1234);
        $allLogger = new AllLogger($mockLogger);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new AggregateLogger([
                $allLogger,
                new NullProcessObserver()
            ])
        );

        $process->sendSignal(Process::SIGTERM);
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
