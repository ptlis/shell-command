<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Unit\Mocks;

use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Mock\MockCommand;
use ptlis\ShellCommand\Mock\MockCommandBuilder;
use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\ProcessOutput;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\Mock\MockCommandBuilder
 */
class MockCommandBuilderTest extends PtlisShellCommandTestcase
{
    public function testMockCommandBuilderOne(): void
    {
        $builder = new MockCommandBuilder(
            [new ProcessOutput(0, 'hello world', '', 'foo \'--foo bar\' \-d 10\' \'if=/bar\' \'wop\'', '.')]
        );

        $builder = $builder
            ->setCommand('foo')
            ->addMockResult(0, 'hello world', '', 'foo \'--foo bar\' \-d 10\' \'if=/bar\' \'wop\'', '.')
            ->addArgument('--foo bar')
            ->addArgument('-d 10')
            ->addArgument('if=/bar')
            ->addArgument('wop');

        $builtCommand = $builder->buildCommand();

        $expectCommand = new MockCommand(
            'foo',
            [
                '--foo bar',
                '-d 10',
                'if=/bar',
                'wop'
            ],
            [],
            new ProcessOutput(0, 'hello world', '', 'foo \'--foo bar\' \-d 10\' \'if=/bar\' \'wop\'', '.')
        );

        $this->assertEquals(
            $expectCommand,
            $builtCommand
        );

        $this->assertEquals(
            [$expectCommand],
            $builder->getBuiltCommands()
        );

        $this->assertEquals(
            new ProcessOutput(0, 'hello world', '', 'foo \'--foo bar\' \-d 10\' \'if=/bar\' \'wop\'', '.'),
            $builtCommand->runSynchronous()
        );
    }

    public function testMockCommandBuilderTwo(): void
    {
        $builder = new MockCommandBuilder();

        $builtCommand = $builder
            ->setCommand('bar')
            ->addArguments(
                [
                    'baz',
                    'bat'
                ]
            )
            ->addMockResult(1, 'hurray!', '', 'bar \'baz\' \'bat\'', '.')
            ->addEnvironmentVariable('key', 'value')
            ->addEnvironmentVariables(['test' => 'message'])
            ->buildCommand();

        $expectCommand = new MockCommand(
            'bar',
            ['baz', 'bat'],
            [],
            new ProcessOutput(1, 'hurray!', '', 'bar \'baz\' \'bat\'', '.')
        );

        $this->assertEquals(
            $expectCommand,
            $builtCommand
        );

        $this->assertEquals(
            [$expectCommand],
            $builder->getBuiltCommands()
        );

        $this->assertEquals(
            new ProcessOutput(1, 'hurray!', '', 'bar \'baz\' \'bat\'', '.'),
            $builtCommand->runSynchronous()
        );

        $this->assertEquals(
            'bar \'baz\' \'bat\'',
            $builtCommand->__toString()
        );
    }

    public function testMockCommandMultiUseOne(): void
    {
        $builder = new MockCommandBuilder();

        $builtCommand1 = $builder
            ->addMockResult(1, 'hurray!', '', 'bar', '.')
            ->setCommand('bar')
            ->buildCommand();

        $expectResult1 = new ProcessOutput(1, 'hurray!', '', 'bar', '.');
        $expectCommand1 = new MockCommand(
            'bar',
            [],
            [],
            new ProcessOutput(1, 'hurray!', '', 'bar', '.')
        );

        $this->assertEquals(
            $expectResult1,
            $builtCommand1->runSynchronous()
        );

        $builtCommand2 = $builder
            ->setCommand('baz')
            ->addMockResult(0, 'success', '', 'baz', '.')
            ->buildCommand();

        $expectResult2 = new ProcessOutput(0, 'success', '', 'baz', '.');
        $expectCommand2 = new MockCommand(
            'baz',
            [],
            [],
            new ProcessOutput(0, 'success', '', 'baz', '.')
        );

        $this->assertEquals(
            $expectResult2,
            $builtCommand2->runSynchronous()
        );

        $this->assertEquals(
            [$expectCommand1, $expectCommand2],
            $builder->getBuiltCommands()
        );
    }

    public function testMockCommandMultiUseTwo(): void
    {
        $builder = new MockCommandBuilder();
        $primedBuilder = $builder
            ->addMockResult(1, 'hurray!', '', 'bar', '.')
            ->addMockResult(0, 'success', '', 'baz', '.');

        $builtCommand1 = $primedBuilder
            ->setCommand('bar')
            ->buildCommand();

        $expectResult1 = new ProcessOutput(1, 'hurray!', '', 'bar', '.');
        $expectCommand1 = new MockCommand(
            'bar',
            [],
            [],
            new ProcessOutput(1, 'hurray!', '', 'bar', '.')
        );

        $this->assertEquals(
            $expectResult1,
            $builtCommand1->runSynchronous()
        );

        $builtCommand2 = $primedBuilder
            ->setCommand('baz')
            ->buildCommand();

        $expectResult2 = new ProcessOutput(0, 'success', '', 'baz', '.');
        $expectCommand2 = new MockCommand(
            'baz',
            [],
            [],
            new ProcessOutput(0, 'success', '', 'baz', '.')
        );

        $this->assertEquals(
            $expectResult2,
            $builtCommand2->runSynchronous()
        );

        $this->assertEquals(
            [$expectCommand1, $expectCommand2],
            $builder->getBuiltCommands()
        );
    }

    public function testPollTimeout(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new MockCommandBuilder();

        $partialBuilder = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setPollTimeout(1000000);

        $results = [];
        $this->assertEquals(
            new MockCommandBuilder(
                $results,
                $path,
                $arguments,
                1000000,
                -1
            ),
            $partialBuilder
        );
    }

    public function testTimeout(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new MockCommandBuilder();

        $partialBuilder = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setTimeout(60 * 1000 * 1000);

        $results = [];
        $this->assertEquals(
            new MockCommandBuilder(
                $results,
                $path,
                $arguments,
                1000,
                60 * 1000 * 1000
            ),
            $partialBuilder
        );
    }

    public function testSetCwd(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new MockCommandBuilder();

        $partialBuilder = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setCwd('/foo/bar');

        $results = [];
        $this->assertEquals(
            new MockCommandBuilder(
                $results,
                $path,
                $arguments,
                1000,
                -1,
                '/foo/bar'
            ),
            $partialBuilder
        );
    }

    public function testAddEnvironment(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new MockCommandBuilder();

        $partialBuilder = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->addEnvironmentVariable('VARIABLE', 'value');

        $results = [];
        $this->assertEquals(
            new MockCommandBuilder(
                $results,
                $path,
                $arguments,
                1000,
                -1,
                '',
                ['VARIABLE' => 'value']
            ),
            $partialBuilder
        );
    }

    public function testInvalidBinary(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'No command was provided to "ptlis\ShellCommand\Mock\MockCommandBuilder", unable to build command.'
        );
        $builder = new MockCommandBuilder();

        $builder->buildCommand();
    }

    public function testClearOne(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No result was provided for use when mocking execution of the command.');

        $builder = new MockCommandBuilder();
        $builder
            ->setCommand('foo')
            ->buildCommand();
    }

    public function testClearTwo(): void
    {
        $builder = new MockCommandBuilder();

        $command = $builder
            ->setCommand('foo')
            ->addArgument('--test')
            ->addMockResult(0, 'bar', '', 'foo \'--test\'', '.')
            ->buildCommand();

        $this->assertEquals(
            'foo \'--test\'',
            $command->__toString()
        );
    }

    public function testTooFewReturns(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No result was provided for use when mocking execution of the command.');

        $builder = new MockCommandBuilder();

        $builder
            ->setCommand('foo')
            ->addArgument('--test')
            ->buildCommand();
    }

    public function testAddRawArgument(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new MockCommandBuilder();

        $partialBuilder = $builder
            ->setCommand($path)
            ->addRawArgument('--foo');

        $results = [];
        $builtCommandList = [];
        $this->assertEquals(
            new MockCommandBuilder(
                $results,
                $path,
                [],
                1000,
                -1,
                '',
                [],
                $builtCommandList,
                ['--foo']
            ),
            $partialBuilder
        );
    }

    public function testAddRawArguments(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new MockCommandBuilder();

        $partialBuilder = $builder
            ->setCommand($path)
            ->addRawArguments(['--foo', '--bar']);

        $results = [];
        $builtCommandList = [];
        $this->assertEquals(
            new MockCommandBuilder(
                $results,
                $path,
                [],
                1000,
                -1,
                '',
                [],
                $builtCommandList,
                ['--foo', '--bar']
            ),
            $partialBuilder
        );
    }

    public function testAddProcessObserver(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new MockCommandBuilder();

        $observer = new NullProcessObserver();

        $partialBuilder = $builder
            ->setCommand($path)
            ->addProcessObserver($observer);

        $results = [];
        $builtCommandList = [];
        $this->assertEquals(
            new MockCommandBuilder(
                $results,
                $path,
                [],
                1000,
                -1,
                '',
                [],
                $builtCommandList,
                [],
                [$observer]
            ),
            $partialBuilder
        );
    }
}
