<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit\CommandBuilder;

use ptlis\ShellCommand\Logger\ProcessExitedLogger;
use ptlis\ShellCommand\Logger\ProcessStartedLogger;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Mock\MockEnvironment;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\CommandBuilder;

class CommandBuilderTest extends ptlisShellCommandTestcase
{
    public function testBasic()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new MockEnvironment());

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
        $builder = new CommandBuilder(new MockEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('--foo bar')
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\'',
            $command->__toString()
        );
    }

    public function testArgumentList()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new MockEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments(
                array(
                    '--foo bar',
                    'baz'
                )
            )
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\' \'baz\'',
            $command->__toString()
        );
    }

    public function testPollTimeout()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = array(
            '--foo bar',
            'baz'
        );
        $builder = new CommandBuilder(new MockEnvironment());

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
        $arguments = array(
            '--foo bar',
            'baz'
        );
        $builder = new CommandBuilder(new MockEnvironment());

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
        $arguments = array(
            '--foo bar',
            'baz'
        );
        $builder = new CommandBuilder(new MockEnvironment());

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

    public function testAddSingleLogger()
    {
        $builder = new CommandBuilder(new MockEnvironment());

        $logger = new MockPsrLogger();

        $command = $builder
            ->addArgument('test')
            ->setCommand('./tests/commands/unix/test_binary')
            ->addProcessObserver(
                new ProcessExitedLogger($logger)
            )
            ->buildCommand();

        $this->assertInstanceOf(
            'ptlis\ShellCommand\Logger\ProcessExitedLogger',
            \PHPUnit_Framework_TestCase::readAttribute($command, 'processObserver')
        );
    }

    public function testAddMultipleLogger()
    {
        $builder = new CommandBuilder(new MockEnvironment());

        $logger = new MockPsrLogger();

        $command = $builder
            ->addArgument('test')
            ->setCommand('./tests/commands/unix/test_binary')
            ->addProcessObserver(
                new ProcessStartedLogger($logger)
            )
            ->addProcessObserver(
                new ProcessExitedLogger($logger)
            )
            ->buildCommand();

        $this->assertInstanceOf(
            'ptlis\ShellCommand\Logger\AggregateLogger',
            \PHPUnit_Framework_TestCase::readAttribute($command, 'processObserver')
        );

        // Todo test internals of AggregateLogger?
    }
}
