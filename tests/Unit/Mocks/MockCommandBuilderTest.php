<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Mocks;

use ptlis\ShellCommand\Mock\MockCommand;
use ptlis\ShellCommand\Mock\MockCommandBuilder;
use ptlis\ShellCommand\Mock\MockEnvironment;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\ProcessOutput;

class MockCommandBuilderTest extends ptlisShellCommandTestcase
{
    public function testMockCommandBuilderOne()
    {
        $builder = new MockCommandBuilder();

        $builder = $builder
            ->setCommand('foo')
            ->addMockResult(0, 'hello world', '')
            ->addArgument('--foo bar')
            ->addArgument('-d 10')
            ->addArgument('if=/bar')
            ->addArgument('wop');

        $builtCommand = $builder->buildCommand();

        $expectCommand = new MockCommand(
            new MockEnvironment(),
            'foo',
            [
                '--foo bar',
                '-d 10',
                'if=/bar',
                'wop'
            ],
            [],
            new ProcessOutput(0, 'hello world', '')
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
            new ProcessOutput(0, 'hello world', ''),
            $builtCommand->runSynchronous()
        );
    }

    public function testMockCommandBuilderTwo()
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
            ->addMockResult(1, 'hurray!', '')
            ->addEnvironmentVariable('key', 'value')
            ->buildCommand();

        $expectCommand = new MockCommand(
            new MockEnvironment(),
            'bar',
            ['baz', 'bat'],
            [],
            new ProcessOutput(1, 'hurray!', ''),
            ['key' => 'value']
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
            new ProcessOutput(1, 'hurray!', ''),
            $builtCommand->runSynchronous()
        );

        $this->assertEquals(
            'key=\'value\' bar \'baz\' \'bat\'',
            $builtCommand->__toString()
        );
    }

    public function testMockCommandMultiUseOne()
    {
        $builder = new MockCommandBuilder();

        $builtCommand1 = $builder
            ->addMockResult(1, 'hurray!', '')
            ->setCommand('bar')
            ->buildCommand();

        $expectResult1 = new ProcessOutput(1, 'hurray!', '');
        $expectCommand1 = new MockCommand(
            new MockEnvironment(),
            'bar',
            [],
            [],
            new ProcessOutput(1, 'hurray!', '')
        );

        $this->assertEquals(
            $expectResult1,
            $builtCommand1->runSynchronous()
        );

        $builtCommand2 = $builder
            ->setCommand('baz')
            ->addMockResult(0, 'success', '')
            ->buildCommand();

        $expectResult2 = new ProcessOutput(0, 'success', '');
        $expectCommand2 = new MockCommand(
            new MockEnvironment(),
            'baz',
            [],
            [],
            new ProcessOutput(0, 'success', '')
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

    public function testMockCommandMultiUseTwo()
    {
        $builder = new MockCommandBuilder();
        $primedBuilder = $builder
            ->addMockResult(1, 'hurray!', '')
            ->addMockResult(0, 'success', '');

        $builtCommand1 = $primedBuilder
            ->setCommand('bar')
            ->buildCommand();

        $expectResult1 = new ProcessOutput(1, 'hurray!', '');
        $expectCommand1 = new MockCommand(
            new MockEnvironment(),
            'bar',
            [],
            [],
            new ProcessOutput(1, 'hurray!', '')
        );

        $this->assertEquals(
            $expectResult1,
            $builtCommand1->runSynchronous()
        );

        $builtCommand2 = $primedBuilder
            ->setCommand('baz')
            ->buildCommand();

        $expectResult2 = new ProcessOutput(0, 'success', '');
        $expectCommand2 = new MockCommand(
            new MockEnvironment(),
            'baz',
            [],
            [],
            new ProcessOutput(0, 'success', '')
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

    public function testPollTimeout()
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

    public function testTimeout()
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

    public function testSetCwd()
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

    public function testAddEnvironment()
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

    public function testInvalidBinary()
    {
        $this->setExpectedException(
            '\RuntimeException',
            'No command was provided to "ptlis\ShellCommand\Mock\MockCommandBuilder", unable to build command.'
        );
        $builder = new MockCommandBuilder();

        $builder->buildCommand();
    }

    public function testClearOne()
    {
        $this->setExpectedException(
            '\RuntimeException',
            'No result was provided for use when mocking execution of the command.'
        );

        $builder = new MockCommandBuilder();
        $builder
            ->setCommand('foo')
            ->buildCommand();
    }

    public function testClearTwo()
    {
        $builder = new MockCommandBuilder();

        $command = $builder
            ->setCommand('foo')
            ->addArgument('--test')
            ->addMockResult(0, ['bar'], [])
            ->buildCommand();

        $this->assertEquals(
            'foo \'--test\'',
            $command->__toString()
        );
    }

    public function testTooFewReturns()
    {
        $this->setExpectedException(
            '\RuntimeException',
            'No result was provided for use when mocking execution of the command.'
        );

        $builder = new MockCommandBuilder();

        $builder
            ->setCommand('foo')
            ->addArgument('--test')
            ->buildCommand();
    }
}
