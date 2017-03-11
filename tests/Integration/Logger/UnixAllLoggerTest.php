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
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\Process;

class UnixAllLoggerTest extends ptlisShellCommandTestcase
{
    public function testCalled()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/test_binary';

        $mockLogger = new MockPsrLogger();

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new AllLogger(
                $mockLogger
            )
        );
        $process->wait();

        $this->assertLogsMatch(
            [
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => [
                        'command' => './tests/commands/unix/test_binary'
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Read from stdout',
                    'context' => [
                        'stdout' => 'Test command' . PHP_EOL . PHP_EOL
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => [
                        'exit_code' => 0
                    ]
                ]
            ],
            $mockLogger->getLogs()
        );
    }
}
