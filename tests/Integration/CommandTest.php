<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Integration;

use ptlis\ShellCommand\CommandArgumentEscaped;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\Command;
use ptlis\ShellCommand\ProcessOutput;
use ptlis\ShellCommand\UnixEnvironment;
use React\EventLoop\Factory;

/**
 * @covers \ptlis\ShellCommand\Command
 */
class CommandTest extends PtlisShellCommandTestcase
{
    public function testRun(): void
    {
        $path = './tests/commands/unix/test_binary';

        $environment = new UnixEnvironment();
        $command = new Command(
            $environment,
            new NullProcessObserver(),
            $path,
            [
                new CommandArgumentEscaped('if=/dev/sha1 of=/dev/sdb2', $environment)
            ],
            (new UnixEnvironment())->getNormalizedCwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                'Test command' . PHP_EOL . 'if=/dev/sha1 of=/dev/sdb2' . PHP_EOL,
                '',
                './tests/commands/unix/test_binary \'if=/dev/sha1 of=/dev/sdb2\'',
                (new UnixEnvironment())->getNormalizedCwd()
            ),
            $command->runSynchronous()
        );
    }

    public function testRunFromHome(): void
    {
        $originalPath = \getenv('PATH');

        $pathToCommand = \realpath(getcwd() . '/tests/commands/unix');

        \putenv('HOME=' . $pathToCommand);

        $path = '~/test_binary';

        $environment = new UnixEnvironment();
        $command = new Command(
            $environment,
            new NullProcessObserver(),
            $path,
            [
                new CommandArgumentEscaped('if=/dev/sha1 of=/dev/sdb2', $environment)
            ],
            (new UnixEnvironment())->getNormalizedCwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                'Test command' . PHP_EOL . 'if=/dev/sha1 of=/dev/sdb2' . PHP_EOL,
                '',
                '~/test_binary \'if=/dev/sha1 of=/dev/sdb2\'',
                (new UnixEnvironment())->getNormalizedCwd()
            ),
            $command->runSynchronous()
        );

        \putenv('PATH=' . $originalPath);
    }

    public function testRunHomeCwd(): void
    {
        $originalPath = \getenv('PATH');

        $fakeHomePath = \realpath(getcwd() . '/tests/commands/unix');

        \putenv('HOME=' . $fakeHomePath);

        $path = '~/sleep_binary 0.1';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [],
            '~/'
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                '',
                '',
                '~/sleep_binary 0.1',
                $fakeHomePath . '/'
            ),
            $command->runSynchronous()
        );

        putenv('PATH=' . $originalPath);
    }

    public function testRunWithSleep(): void
    {
        $path = './tests/commands/unix/sleep_binary 0.1';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [],
            (new UnixEnvironment())->getNormalizedCwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                '',
                '',
                './tests/commands/unix/sleep_binary 0.1',
                (new UnixEnvironment())->getNormalizedCwd()
            ),
            $command->runSynchronous()
        );
    }

    public function testRunWithEnvVariable(): void
    {
        $path = './tests/commands/unix/echo_env_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [],
            (new UnixEnvironment())->getNormalizedCwd(),
            [
                'TEST_VAR' => 'VALUE'
            ]
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                'VALUE' . PHP_EOL,
                '',
                'TEST_VAR=\'VALUE\' ./tests/commands/unix/echo_env_binary',
                (new UnixEnvironment())->getNormalizedCwd()
            ),
            $command->runSynchronous()
        );
    }

    public function testRunWithError(): void
    {
        $path = './tests/commands/unix/error_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [],
            (new UnixEnvironment())->getNormalizedCwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                5,
                '',
                'Fatal Error' . PHP_EOL,
                './tests/commands/unix/error_binary',
                (new UnixEnvironment())->getNormalizedCwd()
            ),
            $command->runSynchronous()
        );
    }

    public function testRunAsPromiseSuccess(): void
    {
        $eventLoop = Factory::create();

        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [],
            (new UnixEnvironment())->getNormalizedCwd()
        );

        $promise = $command->runAsynchronous()->getPromise($eventLoop);

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

    public function testRunSleepAsPromiseSuccess(): void
    {
        $eventLoop = Factory::create();

        $path = './tests/commands/unix/sleep_binary 0.1';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [],
            (new UnixEnvironment())->getNormalizedCwd()
        );

        $promise = $command->runAsynchronous()->getPromise($eventLoop);

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

    public function testRunAsPromiseFailure(): void
    {
        $eventLoop = Factory::create();

        $path = './tests/commands/unix/error_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [],
            (new UnixEnvironment())->getNormalizedCwd()
        );

        $promise = $command->runAsynchronous()->getPromise($eventLoop);

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
