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
            'great success!' . PHP_EOL . 'All is well',
            'some error message' . PHP_EOL . 'Another line',
            'foo --bar',
            '.'
        );

        $this->assertSame(0, $shellResult->exitCode);
        $this->assertSame('great success!' . PHP_EOL . 'All is well', $shellResult->stdOut);
        $this->assertSame(['great success!', 'All is well'], $shellResult->stdOutLines);
        $this->assertSame('some error message' . PHP_EOL . 'Another line', $shellResult->stdErr);
        $this->assertSame(['some error message', 'Another line'], $shellResult->stdErrLines);
        $this->assertSame('foo --bar', $shellResult->command);
        $this->assertSame('.', $shellResult->workingDirectory);
    }

    public function testDeprecatedMethods(): void
    {
        $shellResult = new ProcessOutput(
            0,
            'great success!' . PHP_EOL . 'All is well',
            'some error message' . PHP_EOL . 'Another line',
            'foo --bar',
            '.'
        );

        $this->assertSame($shellResult->exitCode, $shellResult->getExitCode());
        $this->assertSame($shellResult->stdOut, $shellResult->getStdOut());
        $this->assertSame($shellResult->stdOutLines, $shellResult->getStdOutLines());
        $this->assertSame($shellResult->stdErr, $shellResult->getStdErr());
        $this->assertSame($shellResult->stdErrLines, $shellResult->getStdErrLines());
        $this->assertSame($shellResult->command, $shellResult->getExecutedCommand());
        $this->assertSame($shellResult->workingDirectory, $shellResult->getWorkingDirectory());
        $this->expectDeprecationNotice();
    }
}
