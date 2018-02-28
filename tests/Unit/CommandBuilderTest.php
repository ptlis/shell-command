<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit;

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
        $this->setExpectedException(
            '\RuntimeException',
            'Unable to find Environment for OS "foobar".'
        );

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
            \PHPUnit_Framework_TestCase::readAttribute($command, 'pollTimeout')
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
            \PHPUnit_Framework_TestCase::readAttribute($command, 'cwd')
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
            \PHPUnit_Framework_TestCase::readAttribute($command, 'timeout')
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
            \PHPUnit_Framework_TestCase::readAttribute($command, 'envVariableList')
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
            \PHPUnit_Framework_TestCase::readAttribute($command, 'envVariableList')
        );
    }

    public function testAddSingleLogger()
    {
        $builder = new CommandBuilder(new UnixEnvironment());

        $logger = new MockPsrLogger();

        $command = $builder
            ->addArgument('test')
            ->setCommand('./tests/commands/unix/test_binary')
            ->addProcessObserver(
                new NullProcessObserver($logger)
            )
            ->buildCommand();

        $this->assertInstanceOf(
            'ptlis\ShellCommand\Logger\NullProcessObserver',
            \PHPUnit_Framework_TestCase::readAttribute($command, 'processObserver')
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
                new NullProcessObserver($logger)
            )
            ->buildCommand();

        $this->assertInstanceOf(
            'ptlis\ShellCommand\Logger\AggregateLogger',
            \PHPUnit_Framework_TestCase::readAttribute($command, 'processObserver')
        );

        // Todo test internals of AggregateLogger?
    }
}
