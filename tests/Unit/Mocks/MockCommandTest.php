<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit\Mocks;

use ptlis\ShellCommand\Mock\MockCommand;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\ProcessOutput;
use ptlis\ShellCommand\UnixEnvironment;
use React\EventLoop\Factory;

/**
 * @covers \ptlis\ShellCommand\Mock\MockCommand
 */
class MockCommandTest extends ptlisShellCommandTestcase
{
    public function testRunSynchronous(): void
    {
        $path = 'binary';

        $command = new MockCommand(
            new UnixEnvironment(),
            $path,
            ['foo'],
            ['--test=\'123\''],
            new ProcessOutput(0, 'hello world', '', 'binary \'foo\' --test=\'123\'', '.'),
            ['FOO' => 'bar']
        );

        $this->assertEquals('binary \'foo\' --test=\'123\'', $command->__toString());

        $this->assertEquals(
            new ProcessOutput(0, 'hello world', '', 'binary \'foo\' --test=\'123\'', '.'),
            $command->runSynchronous()
        );
    }

    public function testRunPromiseSuccess(): void
    {
        $path = 'binary';

        $command = new MockCommand(
            new UnixEnvironment(),
            $path,
            ['foo'],
            ['--test=\'123\''],
            new ProcessOutput(0, 'hello world', '', 'binary \'foo\' --test=\'123\'', '.'),
            ['FOO' => 'bar']
        );

        $this->assertEquals('binary \'foo\' --test=\'123\'', $command->__toString());

        $eventLoop = Factory::create();
        $promise = $command->runAsynchronous()->getPromise($eventLoop);

        $successCalled = false;
        $failureCalled = false;

        $promise
            ->then(
                function(ProcessOutput $result) use (&$successCalled) {
                    $successCalled = true;
                    $this->assertEquals(
                        new ProcessOutput(0, 'hello world', '', 'binary \'foo\' --test=\'123\'', '.'),
                        $result
                    );
                },
                function(ProcessOutput $result) use (&$failureCalled) {
                    $failureCalled = true;
                }
            );

        $eventLoop->run();

        $this->assertEquals(
            new ProcessOutput(0, 'hello world', '', 'binary \'foo\' --test=\'123\'', '.'),
            $command->runSynchronous()
        );

        $this->assertTrue($successCalled);
        $this->assertFalse($failureCalled);
    }

    public function testRunPromiseFailure(): void
    {
        $path = 'binary';

        $command = new MockCommand(
            new UnixEnvironment(),
            $path,
            ['foo'],
            ['--test=\'123\''],
            new ProcessOutput(1, 'error', '', '', '.'),
            ['FOO' => 'bar']
        );

        $this->assertEquals('binary \'foo\' --test=\'123\'', $command->__toString());

        $eventLoop = Factory::create();
        $promise = $command->runAsynchronous()->getPromise($eventLoop);

        $successCalled = false;
        $failureCalled = false;

        $promise
            ->then(
                function(ProcessOutput $result) use (&$successCalled) {
                    $successCalled = true;
                },
                function(ProcessOutput $result) use (&$failureCalled) {
                    $failureCalled = true;
                    $this->assertEquals(
                        new ProcessOutput(1, 'error', '', '', '.'),
                        $result
                    );
                }
            );

        $eventLoop->run();

        $this->assertEquals(
            new ProcessOutput(1, 'error', '', '', '.'),
            $command->runSynchronous()
        );

        $this->assertFalse($successCalled);
        $this->assertTrue($failureCalled);
    }
}
