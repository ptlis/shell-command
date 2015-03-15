<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\ShellCommand;

use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\ShellCommand;
use ptlis\ShellCommand\ShellResult;
use ptlis\ShellCommand\UnixEnvironment;

class ShellCommandTest extends ptlisShellCommandTestcase
{
    public function testWithFlag()
    {
        $path = './tests/data/test_binary';

        $command = new ShellCommand(
            new UnixEnvironment(),
            $path,
            array(
                '-s bar'
            ),
            getcwd()
        );

        $this->assertSame(
            $path . ' \'-s bar\'',
            $command->__toString()
        );
    }

    public function testWithArgument()
    {
        $path = './tests/data/test_binary';

        $command = new ShellCommand(
            new UnixEnvironment(),
            $path,
            array(
                '--filter=hide-empty'
            ),
            getcwd()
        );

        $this->assertSame(
            $path . ' \'--filter=hide-empty\'',
            $command->__toString()
        );
    }

    public function testWithParameter()
    {
        $path = './tests/data/test_binary';

        $command = new ShellCommand(
            new UnixEnvironment(),
            $path,
            array(
                'my_files/'
            ),
            getcwd()
        );

        $this->assertSame(
            $path . ' \'my_files/\'',
            $command->__toString()
        );
    }

    public function testWithAdHoc()
    {
        $path = './tests/data/test_binary';

        $command = new ShellCommand(
            new UnixEnvironment(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            ),
            getcwd()
        );

        $this->assertSame(
            $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testRun()
    {
        $path = './tests/data/test_binary';

        $command = new ShellCommand(
            new UnixEnvironment(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            ),
            getcwd()
        );

        $this->assertEquals(
            new ShellResult(
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

        $pathToCommand = realpath(getcwd() . '/tests/data');

        putenv('HOME=' . $pathToCommand);

        $path = '~/test_binary';

        $command = new ShellCommand(
            new UnixEnvironment(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            ),
            getcwd()
        );

        $this->assertEquals(
            new ShellResult(
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

        $pathToCommand = realpath(getcwd() . '/tests/data');

        putenv('HOME=' . $pathToCommand);

        $path = '~/sleep_binary';

        $command = new ShellCommand(
            new UnixEnvironment(),
            $path,
            array(),
            '~/'
        );

        $this->assertEquals(
            new ShellResult(
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
        $path = './tests/data/sleep_binary';

        $command = new ShellCommand(
            new UnixEnvironment(),
            $path,
            array(),
            getcwd()
        );

        $this->assertEquals(
            new ShellResult(
                0,
                '',
                ''
            ),
            $command->runSynchronous()
        );
    }

    public function testRunWithError()
    {
        $path = './tests/data/error_binary';

        $command = new ShellCommand(
            new UnixEnvironment(),
            $path,
            array(),
            getcwd()
        );

        $this->assertEquals(
            new ShellResult(
                5,
                '',
                'Fatal Error' . PHP_EOL
            ),
            $command->runSynchronous()
        );
    }
}
