<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Mocks;

use ptlis\ShellCommand\Mock\MockCommand;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\ProcessOutput;

class MockCommandTest extends ptlisShellCommandTestcase
{
    public function testMockCommand()
    {
        $path = 'binary';

        $command = new MockCommand(
            $path,
            array('foo'),
            new ProcessOutput(0, array('hello world'), ''),
            array('FOO' => 'bar')
        );

        $this->assertEquals('FOO=\'bar\' ' . $path . ' \'foo\'', $command->__toString());

        $this->assertEquals(
            new ProcessOutput(0, array('hello world'), ''),
            $command->runSynchronous()
        );
    }
}
