<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration\RunningProcess;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Interfaces\RunningProcessInterface;
use ptlis\ShellCommand\Logger\SignalSentLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\UnixEnvironment;

class UnixRunningProcessSigTermIgnoredTest extends ptlisShellCommandTestcase
{
    public function testRunProcess()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/sleep_binary';

        $logger = new MockPsrLogger();

        $process = new UnixRunningProcessBlockSigTerm(
            new UnixEnvironment(),
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
                        'signal' => RunningProcessInterface::SIGKILL
                    )
                )
            ),
            $logger->getLogs()
        );
    }
}
