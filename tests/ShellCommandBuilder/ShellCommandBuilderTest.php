<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\ShellCommandBuilder;

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
                'Test command',
                ''
            ),
            $command->run()
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
                'Test command' . PHP_EOL . '--foo bar',
                ''
            ),
            $command->run()
        );
    }

    public function testFlag()
    {
        $path = './tests/data/test_binary';
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('-foo')
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'-foo\'',
            $command->__toString()
        );

        $this->assertEquals(
            new ShellResult(
                0,
                'Test command' . PHP_EOL . '-foo',
                ''
            ),
            $command->run()
        );
    }

    public function testParameter()
    {
        $path = './tests/data/test_binary';
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('wibble')
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'wibble\'',
            $command->__toString()
        );

        $this->assertEquals(
            new ShellResult(
                0,
                'Test command' . PHP_EOL . 'wibble',
                ''
            ),
            $command->run()
        );
    }

    public function testAdHoc()
    {
        $path = './tests/data/test_binary';
        $builder = new ShellCommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('if=/dev/sda1 of=/dev/sdb')
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'if=/dev/sda1 of=/dev/sdb\'',
            $command->__toString()
        );

        $this->assertEquals(
            new ShellResult(
                0,
                'Test command' . PHP_EOL . 'if=/dev/sda1 of=/dev/sdb',
                ''
            ),
            $command->run()
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
