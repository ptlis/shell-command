<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit;

use PHPUnit\Framework\TestCase;
use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\CommandBuilder;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\CommandBuilder
 */
class CommandBuilderTest extends ptlisShellCommandTestcase
{
    public function testDetectEnvironmentSuccess()
    {
        $builder = new CommandBuilder();

        $environment = $builder->getEnvironment('Linux');

        $this->assertEquals(
            new UnixEnvironment(),
            $environment
        );
    }

    public function testDetectEnvironmentError()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to find Environment for OS "foobar".');

        $builder = new CommandBuilder();

        $builder->getEnvironment('foobar');
    }

    public function testBasic()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testArgument()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('--foo bar')
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\'',
            $command->__toString()
        );
    }

    public function testConditionalArgumentFalse()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('--foo bar', false)
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testConditionalArgumentTrue()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('--foo bar', true)
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\'',
            $command->__toString()
        );
    }

    public function testArgumentList()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments(
                [
                    '--foo bar',
                    'baz'
                ]
            )
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\' \'baz\'',
            $command->__toString()
        );
    }

    public function testConditionalArgumentListFalse()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments(
                [
                    '--foo bar',
                    'baz'
                ],
                false
            )
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testConditionalArgumentListTrue()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments(
                [
                    '--foo bar',
                    'baz'
                ],
                true
            )
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\' \'baz\'',
            $command->__toString()
        );
    }

    public function testRawArgument()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArgument('--foo \'bar\'')
            ->buildCommand();

        $this->assertEquals(
            $path . ' --foo \'bar\'',
            $command->__toString()
        );
    }

    public function testConditionalRawArgumentFalse()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArgument('--foo \'bar\'', false)
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testConditionalRawArgumentTrue()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArgument('--foo \'bar\'', true)
            ->buildCommand();

        $this->assertEquals(
            $path . ' --foo \'bar\'',
            $command->__toString()
        );
    }

    public function testRawArgumentList()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArguments(
                [
                    '--foo bar',
                    'baz'
                ]
            )
            ->buildCommand();

        $this->assertEquals(
            $path . ' --foo bar baz',
            $command->__toString()
        );
    }

    public function testRawArgumentFalse()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArguments(
                [
                    '--foo bar',
                    'baz'
                ],
                false
            )
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testRawArgumentTrue()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArguments(
                [
                    '--foo bar',
                    'baz'
                ],
                true
            )
            ->buildCommand();

        $this->assertEquals(
            $path . ' --foo bar baz',
            $command->__toString()
        );
    }

    public function testPollTimeout()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setPollTimeout(1234567)
            ->buildCommand();

        $this->assertSame(
            1234567,
            TestCase::readAttribute($command, 'pollTimeout')
        );
    }

    public function testSetCwd()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setCwd('/bob')
            ->buildCommand();

        $this->assertSame(
            '/bob',
            TestCase::readAttribute($command, 'cwd')
        );
    }

    public function testTimeout()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            60 * 1000 * 1000,
            TestCase::readAttribute($command, 'timeout')
        );
    }

    public function testEnvironmentVariable()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->addEnvironmentVariable('TEST', 'VALUE')
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            ['TEST' => 'VALUE'],
            TestCase::readAttribute($command, 'envVariableList')
        );
    }

    public function testEnvironmentVariableFalse()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->addEnvironmentVariable('TEST', 'VALUE', false)
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            [],
            TestCase::readAttribute($command, 'envVariableList')
        );
    }

    public function testEnvironmentVariableTrue()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->addEnvironmentVariable('TEST', 'VALUE', true)
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            ['TEST' => 'VALUE'],
            TestCase::readAttribute($command, 'envVariableList')
        );
    }

    public function testEnvironmentVariable2()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->addEnvironmentVariables([
                'ABC' => '123',
                'TEST' => 'VALUE'
            ])
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            [
                'ABC' => '123',
                'TEST' => 'VALUE'
            ],
            TestCase::readAttribute($command, 'envVariableList')
        );
    }

    public function testAddSingleLogger()
    {
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->addArgument('test')
            ->setCommand('./tests/commands/unix/test_binary')
            ->addProcessObserver(
                new NullProcessObserver()
            )
            ->buildCommand();

        $this->assertInstanceOf(
            'ptlis\ShellCommand\Logger\NullProcessObserver',
            TestCase::readAttribute($command, 'processObserver')
        );
    }

    public function testAddMultipleLogger()
    {
        $builder = new CommandBuilder(new UnixEnvironment());

        $logger = new MockPsrLogger();

        $command = $builder
            ->addArgument('test')
            ->setCommand('./tests/commands/unix/test_binary')
            ->addProcessObserver(
                new AllLogger($logger)
            )
            ->addProcessObserver(
                new NullProcessObserver()
            )
            ->buildCommand();

        $this->assertInstanceOf(
            'ptlis\ShellCommand\Logger\AggregateLogger',
            TestCase::readAttribute($command, 'processObserver')
        );

        // Todo test internals of AggregateLogger?
    }
}
