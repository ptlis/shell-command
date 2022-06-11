<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Unit\Mocks;

use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Mock\MockProcess;
use ptlis\ShellCommand\ProcessOutput;
use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use React\EventLoop\Factory;

/**
 * @covers \ptlis\ShellCommand\Mock\MockProcess
 */
class MockProcessTest extends PtlisShellCommandTestcase
{
    public function testIsRunning(): void
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', '', 'test-command', '.'));

        $this->assertTrue($process->isRunning());
    }

    public function testWait(): void
    {
        $callbackCalled = false;

        $process = new MockProcess('test-command', new ProcessOutput(0, '', '', 'test-command', '.'), 100);
        $process->wait(function () use (&$callbackCalled) {
            $callbackCalled = true;
        });

        $this->assertFalse($process->isRunning());
        $this->assertTrue($callbackCalled);
    }

    public function testStop(): void
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', '', 'test-command', '.'), 100000);
        $this->assertTrue($process->isRunning());

        $process->stop();
        $this->assertFalse($process->isRunning());
    }

    public function testSignal(): void
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', '', 'test-command', '.'), 100000);
        $this->assertTrue($process->isRunning());

        $process->sendSignal(ProcessInterface::SIGTERM);
        $this->assertFalse($process->isRunning());
    }

    public function testGetPid(): void
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', '', 'test-command', '.'), 1000, 9999);
        $this->assertEquals(9999, $process->getPid());
    }

    public function testGetCommand(): void
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, '', '', 'test-command', '.'), 1000, 9999);
        $this->assertEquals('test-command', $process->getCommand());
    }

    public function testGetPidStopped(): void
    {
        $this->expectException(\RuntimeException::class);

        $process = new MockProcess('test-command', new ProcessOutput(0, '', '', 'test-command', '.'), 1000);
        $process->stop();
        $process->getPid();
    }

    public function testGetExitCode(): void
    {
        $process = new MockProcess('test-command', new ProcessOutput(15, '', '', 'test-command', '.'), 1000);
        $output = $process->stop();

        $this->assertEquals(15, $output->getExitCode());
    }

    public function testReadStdOut(): void
    {
        $process = new MockProcess('test-command', new ProcessOutput(0, 'abc123', '', 'test-command', '.'), 1000, 9999);
        $this->assertEquals('abc123', $process->readOutput(ProcessInterface::STDOUT));
    }

    public function testReadStdErr(): void
    {
        $process = new MockProcess(
            'test-command',
            new ProcessOutput(0, '', 'foo bar baz', 'test-command', '.'),
            1000,
            9999
        );
        $this->assertEquals('foo bar baz', $process->readOutput(ProcessInterface::STDERR));
    }

    public function testWriteInput(): void
    {
        $process = new MockProcess(
            'test-command',
            new ProcessOutput(0, '', 'foo bar baz', 'test-command', '.'),
            1000,
            9999
        );
        $process->writeInput('Hello stdin w/ newline');
        $process->writeInput('Hello stdin w/o newline', ProcessInterface::STDIN, false);
        $this->assertEquals(
            [ProcessInterface::STDIN => ["Hello stdin w/ newline\n","Hello stdin w/o newline"]],
            $process->getInputs()
        );
    }

    public function testRunWithPromiseSuccess(): void
    {
        $eventLoop = Factory::create();

        $promise = (new MockProcess(
            'test-command',
            new ProcessOutput(0, '', 'foo bar baz', 'test-command', '.'),
            1000,
            9999
        ))->getPromise($eventLoop);

        $successCalled = false;
        $failureCalled = false;
        $promise->then(
            function () use (&$successCalled) {
                $successCalled = true;
            },
            function () use (&$failureCalled) {
                $failureCalled = true;
            }
        );

        $eventLoop->run();

        $this->assertTrue($successCalled);
        $this->assertFalse($failureCalled);
    }

    public function testRunWithPromiseError(): void
    {
        $eventLoop = Factory::create();

        $promise = (new MockProcess(
            'test-command',
            new ProcessOutput(1, 'ohno!', 'foo bar baz', 'test-command', '.'),
            1000,
            9999
        ))->getPromise($eventLoop);

        $successCalled = false;
        $failureCalled = false;
        $promise->then(
            function () use (&$successCalled) {
                $successCalled = true;
            },
            function () use (&$failureCalled) {
                $failureCalled = true;
            }
        );

        $eventLoop->run();

        $this->assertFalse($successCalled);
        $this->assertTrue($failureCalled);
    }
}
