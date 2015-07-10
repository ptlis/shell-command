<?php

/**
 * PHP Version 5.3
 *
 * @copyright   (c) 2015 brian ridley
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

class UnixAggregateLoggerTest extends ptlisShellCommandTestcase
{
    public function testAggregateLogger()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/long_sleep_binary';

        $mockLogger = new MockPsrLogger();
        $allLogger = new AllLogger($mockLogger);

        $process = new Process(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new AggregateLogger(array(
                $allLogger,
                new NullProcessObserver()
            ))
        );

        $process->sendSignal(Process::SIGTERM);
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
                        'signal' => 'SIGTERM'
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
            $mockLogger->getLogs()
        );
    }
}
