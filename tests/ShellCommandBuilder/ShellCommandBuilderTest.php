<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\ShellCommandBuilder;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Logger\ProcessExitedLogger;
use ptlis\ShellCommand\Logger\ProcessStartedLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\ShellCommand;
use ptlis\ShellCommand\ShellCommandBuilder;
use ptlis\ShellCommand\ShellResult;
use ptlis\ShellCommand\Test\Logger\MockPsrLogger;
use ptlis\ShellCommand\UnixEnvironment;

class ShellCommandBuilderTest extends ptlisShellCommandTestcase
{
    public function testBasic()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );

        $this->assertEquals(
            new ShellResult(
                0,
                'Test command' . PHP_EOL . PHP_EOL,
                ''
            ),
            $command->runSynchronous()
        );
    }

    public function testArgument()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('--foo bar')
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\'',
            $command->__toString()
        );

        $this->assertEquals(
            new ShellResult(
                0,
                'Test command' . PHP_EOL . '--foo bar' . PHP_EOL,
                ''
            ),
            $command->runSynchronous()
        );
    }

    public function testArgumentList()
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new ShellCommandBuilder(new UnixEnvironment());

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

        $this->assertEquals(
            new ShellResult(
                0,
                'Test command' . PHP_EOL . '--foo bar baz' . PHP_EOL,
                ''
            ),
            $command->runSynchronous()
        );
    }

    public function testPollTimeout()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = array(
            '--foo bar',
            'baz'
        );
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setPollTimeout(1000000)
            ->buildCommand();

        $this->assertEquals(
            new ShellCommand(
                new UnixEnvironment(),
                new NullProcessObserver(),
                $path,
                $arguments,
                getcwd(),
                -1,
                1000000
            ),
            $command
        );
    }

    public function testSetCwd()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = array(
            '--foo bar',
            'baz'
        );
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setCwd('/bob')
            ->buildCommand();

        $this->assertEquals(
            new ShellCommand(
                new UnixEnvironment(),
                new NullProcessObserver(),
                $path,
                $arguments,
                '/bob',
                -1,
                1000
            ),
            $command
        );
    }

    public function testTimeout()
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = array(
            '--foo bar',
            'baz'
        );
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertEquals(
            new ShellCommand(
                new UnixEnvironment(),
                new NullProcessObserver(),
                $path,
                $arguments,
                getcwd(),
                60 * 1000 * 1000,
                1000000
            ),
            $command
        );
    }

    public function testInvalidBinary()
    {
        $this->setExpectedException(
            '\RuntimeException',
            'Invalid command "" provided.'
        );
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $builder->buildCommand();
    }

    public function testClearOne()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Invalid command "foo" provided.'
        );
        $builder = new ShellCommandBuilder(new UnixEnvironment());
        $builder
            ->setCommand('foo')
            ->buildCommand();
    }

    public function testClearTwo()
    {
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $command = $builder
            ->addArgument('test')
            ->setCommand('./tests/commands/unix/test_binary')
            ->buildCommand();

        $this->assertEquals(
            './tests/commands/unix/test_binary \'test\'',
            $command->__toString()
        );
    }

    public function testAddSingleLogger()
    {
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $logger = new MockPsrLogger();

        $command = $builder
            ->addArgument('test')
            ->setCommand('./tests/commands/unix/test_binary')
            ->addProcessObserver(
                new ProcessExitedLogger($logger)
            )
            ->buildCommand();

        $command->runSynchronous();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => array(
                        'exit_code' => 0
                    )
                )
            ),
            $logger->getLogs()
        );
    }

    public function testAddMultipleLogger()
    {
        $builder = new ShellCommandBuilder(new UnixEnvironment());

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

        $command->runSynchronous();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process created',
                    'context' => array(
                        'command' => './tests/commands/unix/test_binary \'test\''
                    )
                ),
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Process exited',
                    'context' => array(
                        'exit_code' => 0
                    )
                )
            ),
            $logger->getLogs()
        );
    }
}
