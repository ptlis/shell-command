<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\RunningProcess;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Logger\SignalSentLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\Test\Logger\MockPsrLogger;

class UnixRunningProcessSigTermIgnoredTest extends ptlisShellCommandTestcase
{
    public function testRunProcess()
    {
        $command = './tests/data/sleep_binary';

        $logger = new MockPsrLogger();

        $process = new UnixRunningProcessBlockSigTerm(
            $command,
            getcwd(),
            -1,
            1000,
            new SignalSentLogger($logger)
        );

        $process->stop();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => array(
                        'signal' => 9
                    )
                )
            ),
            $logger->getLogs()
        );
    }
}
