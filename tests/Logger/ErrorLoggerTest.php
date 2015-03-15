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
use ptlis\ShellCommand\Logger\ErrorLogger;
use ptlis\ShellCommand\UnixRunningProcess;

class ErrorLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testCalled()
    {
        $command = './tests/data/error_binary';

        $mockLogger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            $command,
            getcwd(),
            -1,
            1000,
            new ErrorLogger(
                $mockLogger
            )
        );
        $process->wait();

        $logList = $mockLogger->getLogs();


        $this->assertEquals(
            array(
                'level' => LogLevel::ERROR,
                'message' => 'Process exited',
                'context' => array(
                    'exit_code' => 5
                )
            ),
            $logList[0]
        );

        $this->assertEquals(
            array(
                'level' => LogLevel::ERROR,
                'message' => 'Read from stderr',
                'context' => array(
                    'stderr' => 'Fatal Error' . PHP_EOL
                )
            ),
            $logList[1]
        );
    }

    public function testCalledWithCustomLogLevel()
    {
        $command = './tests/data/error_binary';

        $mockLogger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            $command,
            getcwd(),
            -1,
            1000,
            new ErrorLogger(
                $mockLogger,
                LogLevel::CRITICAL
            )
        );
        $process->wait();

        $logList = $mockLogger->getLogs();


        $this->assertEquals(
            array(
                'level' => LogLevel::CRITICAL,
                'message' => 'Read from stderr',
                'context' => array(
                    'stderr' => 'Fatal Error' . PHP_EOL
                )
            ),
            $logList[1]
        );

        $this->assertEquals(
            array(
                'level' => LogLevel::CRITICAL,
                'message' => 'Process exited',
                'context' => array(
                    'exit_code' => 5
                )
            ),
            $logList[0]
        );
    }

    public function testSendSignal()
    {
        $command = './tests/data/sleep_binary';

        $mockLogger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            $command,
            getcwd(),
            -1,
            1000,
            new ErrorLogger(
                $mockLogger
            )
        );
        $process->stop();

        $logList = $mockLogger->getLogs();

        $this->assertEquals(
            array(
                'level' => LogLevel::ERROR,
                'message' => 'Process exited',
                'context' => array(
                    'exit_code' => -1
                )
            ),
            $logList[0]
        );
    }
}
