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
use ptlis\ShellCommand\Logger\ProcessStartedLogger;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\UnixRunningProcess;

class UnixProcessStartedLoggerTest extends ptlisShellCommandTestcase
{
    public function testCalled()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/test_binary';

        $mockLogger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new ProcessStartedLogger(
                $mockLogger,
                LogLevel::DEBUG
            )
        );
        $process->wait();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => array(
                        'command' => './tests/commands/unix/test_binary'
                    )
                )
            ),
            $mockLogger->getLogs()
        );
    }
}
