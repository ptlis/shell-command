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

namespace ptlis\ShellCommand\Test\Logger;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Logger\ProcessExitedLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixRunningProcess;

class ProcessExitedLoggerTest extends ptlisShellCommandTestcase
{
    public function testCalled()
    {
        $command = './tests/data/test_binary';

        $mockLogger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            $command,
            getcwd(),
            -1,
            1000,
            new ProcessExitedLogger(
                $mockLogger,
                LogLevel::DEBUG
            )
        );
        $process->wait();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => array(
                        'exit_code' => 0
                    )
                )
            ),
            $mockLogger->getLogs()
        );
    }

    public function testStopped()
    {
        $command = './tests/data/sleep_binary';

        $mockLogger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            $command,
            getcwd(),
            -1,
            1000,
            new ProcessExitedLogger(
                $mockLogger,
                LogLevel::DEBUG
            )
        );
        $process->stop();

        $this->assertLogsMatch(
            array(
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
