<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Mocks;

use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Mock\MockProcess;
use ptlis\ShellCommand\ProcessOutput;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;

/**
 * @covers \ptlis\ShellCommand\Mock\MockProcess
 */
class MockProcessTest extends ptlisShellCommandTestcase
{
    public function testIsRunning()
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''));

        $this->assertTrue($process->isRunning());
    }

    public function testWait()
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''), 100);
        $process->wait();

        $this->assertFalse($process->isRunning());
    }

    public function testStop()
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''), 100000);
        $this->assertTrue($process->isRunning());

        $process->stop();
        $this->assertFalse($process->isRunning());
    }

    public function testSignal()
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''), 100000);
        $this->assertTrue($process->isRunning());

        $process->sendSignal(ProcessInterface::SIGTERM);
        $this->assertFalse($process->isRunning());
    }

    public function testGetPid()
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''), 1000, 9999);
        $this->assertEquals(9999, $process->getPid());
    }

    public function testGetCommand()
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''), 1000, 9999);
        $this->assertEquals('test-command', $process->getCommand());
    }

    public function testGetPidStopped()
    {
        $this->setExpectedException('\RuntimeException');

        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''), 1000);
        $process->stop();
        $process->getPid();
    }

    public function testGetExitCode()
    {
        $process = new MockProcess('test-command', new ProcessOutput(15, '', ''), 1000);
        $process->stop();

        $this->assertEquals(15, $process->getExitCode());
    }

    public function testReadStdOut()
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, 'abc123', ''), 1000, 9999);
        $this->assertEquals('abc123', $process->readOutput(ProcessInterface::STDOUT));
    }

    public function testReadStdErr()
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', 'foo bar baz'), 1000, 9999);
        $this->assertEquals('foo bar baz', $process->readOutput(ProcessInterface::STDERR));
    }

    public function testGetExitCodeRunning()
    {
        $this->setExpectedException('\RuntimeException');

        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''), 1000);
        $process->getExitCode();
    }
}
