<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Mocks;

use ptlis\ShellCommand\Mock\MockCommand;
use ptlis\ShellCommand\Mock\MockEnvironment;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\ProcessOutput;

class MockCommandTest extends ptlisShellCommandTestcase
{
    public function testMockCommand()
    {
        $path = 'binary';

        $command = new MockCommand(
            new MockEnvironment(),
            $path,
            ['foo'],
            new ProcessOutput(0, ['hello world'], ''),
            ['FOO' => 'bar']
        );

        $this->assertEquals('FOO=\'bar\' ' . $path . ' \'foo\'', $command->__toString());

        $this->assertEquals(
            new ProcessOutput(0, ['hello world'], ''),
            $command->runSynchronous()
        );
    }
}
