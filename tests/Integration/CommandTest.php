<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration;

use ptlis\ShellCommand\CommandArgumentEscaped;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\Command;
use ptlis\ShellCommand\ProcessOutput;
use ptlis\ShellCommand\UnixEnvironment;
use React\EventLoop\Factory;

/**
 * @covers \ptlis\ShellCommand\Command
 */
class CommandTest extends ptlisShellCommandTestcase
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
            getcwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                'Test command' . PHP_EOL . 'if=/dev/sha1 of=/dev/sdb2' . PHP_EOL,
                '',
                './tests/commands/unix/test_binary \'if=/dev/sha1 of=/dev/sdb2\'',
                getcwd()
            ),
            $command->runSynchronous()
        );
    }

    public function testRunFromHome(): void
    {
        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/commands/unix');

        putenv('HOME=' . $pathToCommand);

        $path = '~/test_binary';

        $environment = new UnixEnvironment();
        $command = new Command(
            $environment,
            new NullProcessObserver(),
            $path,
            [
                new CommandArgumentEscaped('if=/dev/sha1 of=/dev/sdb2', $environment)
            ],
            getcwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                'Test command' . PHP_EOL . 'if=/dev/sha1 of=/dev/sdb2' . PHP_EOL,
                '',
                '~/test_binary \'if=/dev/sha1 of=/dev/sdb2\'',
                getcwd()
            ),
            $command->runSynchronous()
        );

        putenv('PATH=' . $originalPath);
    }

    public function testRunHomeCwd(): void
    {
        $originalPath = getenv('PATH');

        $fakeHomePath = realpath(getcwd() . '/tests/commands/unix');


        putenv('HOME=' . $fakeHomePath);

        $path = '~/sleep_binary';

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
                '~/sleep_binary',
                $fakeHomePath . '/'
            ),
            $command->runSynchronous()
        );

        putenv('PATH=' . $originalPath);
    }

    public function testRunWithSleep(): void
    {
        $path = './tests/commands/unix/sleep_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [],
            getcwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                '',
                '',
                './tests/commands/unix/sleep_binary',
                getcwd()
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
            getcwd(),
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
                getcwd()
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
            getcwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                5,
                '',
                'Fatal Error' . PHP_EOL,
                './tests/commands/unix/error_binary',
                getcwd()
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
            getcwd()
        );

        $promise = $command->runAsynchronous()->getPromise($eventLoop);

        $successCalled = false;
        $failureCalled = false;

        $promise->then(
            function() use (&$successCalled) {
                $successCalled = true;
            },
            function() use (&$failureCalled) {
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

        $path = './tests/commands/unix/sleep_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [],
            getcwd()
        );

        $promise = $command->runAsynchronous()->getPromise($eventLoop);

        $successCalled = false;
        $failureCalled = false;

        $promise->then(
            function() use (&$successCalled) {
                $successCalled = true;
            },
            function() use (&$failureCalled) {
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
            getcwd()
        );

        $promise = $command->runAsynchronous()->getPromise($eventLoop);

        $successCalled = false;
        $failureCalled = false;

        $promise->then(
            function() use (&$successCalled) {
                $successCalled = true;
            },
            function() use (&$failureCalled) {
                $failureCalled = true;
            }
        );

        $eventLoop->run();

        $this->assertFalse($successCalled);
        $this->assertTrue($failureCalled);
    }
}
