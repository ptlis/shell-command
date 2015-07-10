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
use ptlis\ShellCommand\Logger\StdOutReadLogger;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\Process;

class UnixStdOutReadLoggerTest extends ptlisShellCommandTestcase
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
            new StdOutReadLogger(
                $mockLogger,
                LogLevel::DEBUG
            )
        );
        $process->wait();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Read from stdout',
                    'context' => array(
                        'stdout' => 'Test command' . PHP_EOL . PHP_EOL
                    )
                )
            ),
            $mockLogger->getLogs()
        );
    }
}
