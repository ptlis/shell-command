<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Integration\Logger;

use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Process;
use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\Logger\NullProcessObserver
 */
class NullLoggerTest extends PtlisShellCommandTestcase
{
    public function testCalled(): void
    {
        $command = './tests/commands/unix/test_binary';

        $nullLogger = new NullProcessObserver();

        $process = new Process(
            new UnixEnvironment(),
            $command,
            (new UnixEnvironment())->getNormalizedCwd(),
            [],
            -1,
            1000,
            $nullLogger
        );
        $process->wait();

        $this->assertEquals(
            new NullProcessObserver(),
            $nullLogger
        );
    }

    public function testSendSignal(): void
    {
        $command = './tests/commands/unix/sleep_binary 0.1';

        $nullLogger = new NullProcessObserver();

        $process = new Process(
            new UnixEnvironment(),
            $command,
            (new UnixEnvironment())->getNormalizedCwd(),
            [],
            -1,
            1000,
            $nullLogger
        );
        $process->stop();

        $this->assertEquals(
            new NullProcessObserver(),
            $nullLogger
        );
    }
}
