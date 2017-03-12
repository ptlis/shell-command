<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Mocks;

use ptlis\ShellCommand\Mock\MockProcess;
use ptlis\ShellCommand\ProcessOutput;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;

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

    public function testGetPid()
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''), 1000, 9999);
        $this->assertEquals(9999, $process->getPid());
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

    public function testGetExitCodeRunning()
    {
        $this->setExpectedException('\RuntimeException');

        $process = new MockProcess('test-command', new ProcessOutput(0, '', ''), 1000);
        $process->getExitCode();
    }
}
