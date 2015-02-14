<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\ShellCommandBuilder;

use ptlis\ShellCommand\ShellCommand;
use ptlis\ShellCommand\ShellCommandBuilder;
use ptlis\ShellCommand\ShellResult;
use ptlis\ShellCommand\UnixEnvironment;

class ShellCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $path = './tests/data/test_binary';
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
        $path = './tests/data/test_binary';
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
        $path = './tests/data/test_binary';
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
        $path = './tests/data/test_binary';
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
        $path = './tests/data/test_binary';
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
        $path = './tests/data/test_binary';
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
            ->setCommand('./tests/data/test_binary')
            ->buildCommand();

        $this->assertEquals(
            './tests/data/test_binary \'test\'',
            $command->__toString()
        );
    }
}
