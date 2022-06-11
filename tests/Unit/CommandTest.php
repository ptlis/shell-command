<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Unit;

use PHPUnit\Framework\TestCase;
use ptlis\ShellCommand\CommandArgumentEscaped;
use ptlis\ShellCommand\CommandArgumentRaw;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Command;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\Command
 */
class CommandTest extends TestCase
{
    public function testWithFlag(): void
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
            $environment->getNormalizedCwd()
        );

        $this->assertSame(
            $path . ' \'-s bar\'',
            $command->__toString()
        );
    }

    public function testWithArgument(): void
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
            $environment->getNormalizedCwd()
        );

        $this->assertSame(
            $path . ' \'--filter=hide-empty\'',
            $command->__toString()
        );
    }

    public function testWithParameter(): void
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
            $environment->getNormalizedCwd()
        );

        $this->assertSame(
            $path . ' \'my_files/\'',
            $command->__toString()
        );
    }

    public function testWithAdHoc(): void
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
            $environment->getNormalizedCwd()
        );

        $this->assertSame(
            $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testWithEnvVariables(): void
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
            $environment->getNormalizedCwd(),
            ['MY_VAR' => 'VALUE']
        );

        $this->assertSame(
            $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testMixedArguments(): void
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
            $environment->getNormalizedCwd()
        );

        $this->assertSame(
            $path . ' \'--foo\' --bar \'--baz\' --bat',
            $command->__toString()
        );
    }
}
