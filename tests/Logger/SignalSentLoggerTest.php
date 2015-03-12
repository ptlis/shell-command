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
use ptlis\ShellCommand\Logger\SignalSentLogger;
use ptlis\ShellCommand\UnixRunningProcess;

class SignalSentLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Note - this test may (in theory) fail - being an integration test there's a chance that
     */
    public function testCalled()
    {
        $command = './tests/data/sleep_binary';

        $mockLogger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            $command,
            getcwd(),
            -1,
            1000,
            new SignalSentLogger(
                $mockLogger,
                LogLevel::DEBUG
            )
        );
        $process->stop();

        $logList = $mockLogger->getLogs();

        $this->assertEquals(
            array(
                'level' => LogLevel::DEBUG,
                'message' => 'Signal Sent: ' . SIGTERM,
                'context' => array()
            ),
            $logList[0]
        );
    }
}
