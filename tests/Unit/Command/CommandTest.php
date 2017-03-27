<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit\ShellCommand;

use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Mock\MockEnvironment;
use ptlis\ShellCommand\Command;

/**
 * @covers \ptlis\ShellCommand\Command
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    public function testWithFlag()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            [
                '-s bar'
            ],
            [],
            getcwd()
        );

        $this->assertSame(
            $path . ' \'-s bar\'',
            $command->__toString()
        );
    }

    public function testWithArgument()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            [
                '--filter=hide-empty'
            ],
            [],
            getcwd()
        );

        $this->assertSame(
            $path . ' \'--filter=hide-empty\'',
            $command->__toString()
        );
    }

    public function testWithParameter()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            [
                'my_files/'
            ],
            [],
            getcwd()
        );

        $this->assertSame(
            $path . ' \'my_files/\'',
            $command->__toString()
        );
    }

    public function testWithAdHoc()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            [
                'if=/dev/sha1 of=/dev/sdb2'
            ],
            [],
            getcwd()
        );

        $this->assertSame(
            $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testWithEnvVariables()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            [
                'if=/dev/sha1 of=/dev/sdb2'
            ],
            [],
            getcwd(),
            ['MY_VAR' => 'VALUE']
        );

        $this->assertSame(
            'MY_VAR=\'VALUE\' ' . $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }
}
