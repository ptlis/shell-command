<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration\Environment;

use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\Command;
use ptlis\ShellCommand\ProcessOutput;
use ptlis\ShellCommand\UnixEnvironment;

class UnixCommandTest extends ptlisShellCommandTestcase
{
    public function testRun()
    {
        $this->skipIfNotUnix();

        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            ),
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
        $this->skipIfNotUnix();

        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/commands/unix');

        putenv('HOME=' . $pathToCommand);

        $path = '~/test_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            ),
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
        $this->skipIfNotUnix();

        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/commands/unix');

        putenv('HOME=' . $pathToCommand);

        $path = '~/sleep_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            array(),
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
        $this->skipIfNotUnix();

        $path = './tests/commands/unix/sleep_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            array(),
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
        $this->skipIfNotUnix();

        $path = './tests/commands/unix/echo_env';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            array(),
            getcwd(),
            array(
                'TEST_VAR' => 'VALUE'
            )
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
        $this->skipIfNotUnix();

        $path = './tests/commands/unix/error_binary';

        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            array(),
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
}
