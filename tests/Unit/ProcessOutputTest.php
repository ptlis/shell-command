<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test;

use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\ProcessOutput;

/**
 * @covers \ptlis\ShellCommand\ProcessOutput
 */
class ProcessOutputTest extends ptlisShellCommandTestcase
{
    public function testProcessOutput(): void
    {
        $shellResult = new ProcessOutput(
            0,
            'great success!',
            '',
            'foo --bar',
            '.'
        );

        $this->assertSame(
            0,
            $shellResult->getExitCode()
        );

        $this->assertSame(
            'great success!',
            $shellResult->getStdOut()
        );

        $this->assertSame(
            ['great success!'],
            $shellResult->getStdOutLines()
        );

        $this->assertSame(
            '',
            $shellResult->getStdErr()
        );

        $this->assertSame(
            [],
            $shellResult->getStdErrLines()
        );

        $this->assertSame(
            'foo --bar',
            $shellResult->getExecutedCommand()
        );

        $this->assertSame(
            '.',
            $shellResult->getWorkingDirectory()
        );
    }
}
