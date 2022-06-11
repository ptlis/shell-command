<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Unit;

use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\ProcessOutput;

/**
 * @covers \ptlis\ShellCommand\ProcessOutput
 */
class ProcessOutputTest extends PtlisShellCommandTestcase
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
