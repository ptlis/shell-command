<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration\Process;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\UnixEnvironment;

class UnixProcessSigTermIgnoredTest extends ptlisShellCommandTestcase
{
    public function testRunProcess()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/sleep_binary';

        $logger = new MockPsrLogger();

        $process = new ProcessBlockSigTerm(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new AllLogger($logger)
        );

        $process->stop();

        $this->assertLogsMatch(
            [
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => [
                        'command' => './tests/commands/unix/sleep_binary'
                    ]
                ],
                [
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => [
                        'signal' => ProcessInterface::SIGKILL
                    ]
                ]
            ],
            $logger->getLogs()
        );
    }
}
