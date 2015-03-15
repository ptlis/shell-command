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
use ptlis\ShellCommand\UnixRunningProcess;

class ProcessExitedLoggerTest extends \PHPUnit_Framework_TestCase
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

        $logList = $mockLogger->getLogs();

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Process exited',
                'context' => array(
                    'exit_code' => 0
                )
            ),
            $logList[0]
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

        $logList = $mockLogger->getLogs();

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Process exited',
                'context' => array(
                    'exit_code' => -1
                )
            ),
            $logList[0]
        );
    }
}
