<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Integration;

use ptlis\ShellCommand\CommandBuilder;
use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\CommandBuilder
 */
class CommandBuilderTest extends PtlisShellCommandTestcase
{
    public function testInvalidCommand(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid command "foobar" provided to ptlis\ShellCommand\CommandBuilder.');

        $builder = new CommandBuilder(new UnixEnvironment());

        $builder
            ->setCommand('foobar')
            ->buildCommand();
    }

    public function testWithCwd(): void
    {
        $builder = new CommandBuilder();

        $command = $builder
            ->setCwd(realpath(__DIR__ . '/../commands/unix/'))
            ->setCommand('./test_binary')
            ->buildCommand();

        $result = $command->runSynchronous();

        $this->assertEquals(
            "Test command" . PHP_EOL . PHP_EOL,
            $result->getStdOut()
        );
    }
}
