<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit;

use ptlis\ShellCommand\CommandArgumentEscaped;
use ptlis\ShellCommand\CommandArgumentRaw;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Command;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\Command
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    public function testWithFlag()
    {
        $path = './tests/commands/unix/test_binary';

        $environment = new UnixEnvironment();
        $command = new Command(
            $environment,
            new NullProcessObserver(),
            $path,
            [
                new CommandArgumentEscaped('-s bar', $environment)
            ],
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

        $environment = new UnixEnvironment();
        $command = new Command(
            $environment,
            new NullProcessObserver(),
            $path,
            [
                new CommandArgumentEscaped('--filter=hide-empty', $environment)
            ],
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

        $environment = new UnixEnvironment();
        $command = new Command(
            $environment,
            new NullProcessObserver(),
            $path,
            [
                new CommandArgumentEscaped('my_files/', $environment)

            ],
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

        $this->assertSame(
            $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testWithEnvVariables()
    {
        $path = './tests/commands/unix/test_binary';

        $environment = new UnixEnvironment();
        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [
                new CommandArgumentEscaped('if=/dev/sha1 of=/dev/sdb2', $environment)
            ],
            getcwd(),
            ['MY_VAR' => 'VALUE']
        );

        $this->assertSame(
            'MY_VAR=\'VALUE\' ' . $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testMixedArguments()
    {
        $path = './tests/commands/unix/test_binary';

        $environment = new UnixEnvironment();
        $command = new Command(
            new UnixEnvironment(),
            new NullProcessObserver(),
            $path,
            [
                new CommandArgumentEscaped('--foo', $environment),
                new CommandArgumentRaw('--bar'),
                new CommandArgumentEscaped('--baz', $environment),
                new CommandArgumentRaw('--bat')
            ],
            getcwd()
        );

        $this->assertSame(
            $path . ' \'--foo\' --bar \'--baz\' --bat',
            $command->__toString()
        );
    }
}
