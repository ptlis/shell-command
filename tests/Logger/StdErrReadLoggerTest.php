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
use ptlis\ShellCommand\Logger\StdErrReadLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\UnixRunningProcess;

class StdErrReadLoggerTest extends ptlisShellCommandTestcase
{
    public function testCalled()
    {
        $command = './tests/commands/unix/error_binary';

        $mockLogger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new StdErrReadLogger(
                $mockLogger,
                LogLevel::DEBUG
            )
        );
        $process->wait();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Read from stderr',
                    'context' => array(
                        'stderr' => 'Fatal Error' . PHP_EOL
                    )
                )
            ),
            $mockLogger->getLogs()
        );
    }
}
