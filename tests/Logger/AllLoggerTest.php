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
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\UnixRunningProcess;

class AllLoggerTest extends \PHPUnit_Framework_TestCase
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
            new AllLogger(
                $mockLogger
            )
        );
        $process->wait();

        $logList = $mockLogger->getLogs();

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Process created with command: ./tests/data/test_binary',
                'context' => array()
            ),
            $logList[0]
        );

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Read from stdout: Test command' . PHP_EOL . PHP_EOL,
                'context' => array()
            ),
            $logList[1]
        );

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Read from stderr: ',
                'context' => array()
            ),
            $logList[2]
        );

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Process exited with code 0',
                'context' => array()
            ),
            $logList[3]
        );

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Read from stdout: ',
                'context' => array()
            ),
            $logList[4]
        );

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Read from stderr: ',
                'context' => array()
            ),
            $logList[5]
        );
    }
}
