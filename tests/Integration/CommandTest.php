<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration;

use ptlis\ShellCommand\CommandArgumentEscaped;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Promise\ReactDeferredFactory;
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
    public function testRun()
    {
        $path = './tests/commands/unix/test_binary';

        $environment = new UnixEnvironment();
        $command = new Command(
            $environment,
            new ReactDeferredFactory(),
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
                ''
            ),
            $command->runSynchronous()
        );
    }

    public function testRunFromHome()
    {
        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/commands/unix');

        putenv('HOME=' . $pathToCommand);

        $path = '~/test_binary';

        $environment = new UnixEnvironment();
        $command = new Command(
            $environment,
            new ReactDeferredFactory(),
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
                ''
            ),
            $command->runSynchronous()
        );

        putenv('PATH=' . $originalPath);
    }

    public function testRunHomeCwd()
    {
        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/commands/unix');

        putenv('HOME=' . $pathToCommand);

        $path = '~/sleep_binary';

        $command = new Command(
            new UnixEnvironment(),
            new ReactDeferredFactory(),
            new NullProcessObserver(),
            $path,
            [],
            '~/'
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                '',
                ''
            ),
            $command->runSynchronous()
        );

        putenv('PATH=' . $originalPath);
    }

    public function testRunWithSleep()
    {
        $path = './tests/commands/unix/sleep_binary';

        $command = new Command(
            new UnixEnvironment(),
            new ReactDeferredFactory(),
            new NullProcessObserver(),
            $path,
            [],
            getcwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                0,
                '',
                ''
            ),
            $command->runSynchronous()
        );
    }

    public function testRunWithEnvVariable()
    {
        $path = './tests/commands/unix/echo_env_binary';

        $command = new Command(
            new UnixEnvironment(),
            new ReactDeferredFactory(),
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
                ''
            ),
            $command->runSynchronous()
        );
    }

    public function testRunWithError()
    {
        $path = './tests/commands/unix/error_binary';

        $command = new Command(
            new UnixEnvironment(),
            new ReactDeferredFactory(),
            new NullProcessObserver(),
            $path,
            [],
            getcwd()
        );

        $this->assertEquals(
            new ProcessOutput(
                5,
                '',
                'Fatal Error' . PHP_EOL
            ),
            $command->runSynchronous()
        );
    }

    public function testRunAsPromiseSuccess()
    {
        $eventLoop = Factory::create();

        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new UnixEnvironment(),
            new ReactDeferredFactory(),
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

    public function testRunSleepAsPromiseSuccess()
    {
        $eventLoop = Factory::create();

        $path = './tests/commands/unix/sleep_binary';

        $command = new Command(
            new UnixEnvironment(),
            new ReactDeferredFactory(),
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

    public function testRunAsPromiseFailure()
    {
        $eventLoop = Factory::create();

        $path = './tests/commands/unix/error_binary';

        $command = new Command(
            new UnixEnvironment(),
            new ReactDeferredFactory(),
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
