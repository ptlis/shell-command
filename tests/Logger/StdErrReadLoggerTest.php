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
use ptlis\ShellCommand\UnixRunningProcess;

class StdErrReadLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Note - this test may (in theory) fail - being an integration test there's a chance that
     */
    public function testCalled()
    {
        $command = './tests/data/error_binary';

        $mockLogger = new MockPsrLogger();

        $process = new UnixRunningProcess(
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

        $logList = $mockLogger->getLogs();

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Read from stderr: Fatal Error' . PHP_EOL,
                'context' => array()
            ),
            $logList[0]
        );
    }
}
