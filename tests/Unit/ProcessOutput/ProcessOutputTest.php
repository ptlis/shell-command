<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\ProcessOutput;

use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\ProcessOutput;

class ProcessOutputTest extends ptlisShellCommandTestcase
{
    public function testProcessOutput()
    {
        $shellResult = new ProcessOutput(
            0,
            'great success!',
            ''
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
    }
}
