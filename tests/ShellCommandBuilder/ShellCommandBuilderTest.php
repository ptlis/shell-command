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
use ptlis\ShellCommand\UnixBinary;

class ShellCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $path = './tests/data/empty_binary';
        $builder = new ShellCommandBuilder();

        $command = $builder
            ->setBinary(new UnixBinary($path))
            ->getCommand();

        $this->assertEquals(
            realpath($path),
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
        $path = './tests/data/empty_binary';
        $builder = new ShellCommandBuilder();

        $command = $builder
            ->setBinary(new UnixBinary($path))
            ->addArgument('foo', 'bar')
            ->getCommand();

        $this->assertEquals(
            realpath($path) . ' \'--foo bar\'',
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
        $path = './tests/data/empty_binary';
        $builder = new ShellCommandBuilder();

        $command = $builder
            ->setBinary(new UnixBinary($path))
            ->addFlag('foo')
            ->getCommand();

        $this->assertEquals(
            realpath($path) . ' \'-foo\'',
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
        $path = './tests/data/empty_binary';
        $builder = new ShellCommandBuilder();

        $command = $builder
            ->setBinary(new UnixBinary($path))
            ->addParameter('wibble')
            ->getCommand();

        $this->assertEquals(
            realpath($path) . ' \'wibble\'',
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
        $path = './tests/data/empty_binary';
        $builder = new ShellCommandBuilder();

        $command = $builder
            ->setBinary(new UnixBinary($path))
            ->addAdHoc('if=/dev/sda1 of=/dev/sdb')
            ->getCommand();

        $this->assertEquals(
            realpath($path) . ' \'if=/dev/sda1 of=/dev/sdb\'',
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
            'No binary was provided to "ptlis\ShellCommand\ShellCommandBuilder", unable to build command.'
        );
        $builder = new ShellCommandBuilder();

        $builder->getCommand();
    }

    public function testClearOne()
    {
        $this->setExpectedException(
            'ptlis\ShellCommand\Exceptions\InvalidBinaryException',
            'Binary file "foo" not found or not executable.'
        );
        $builder = new ShellCommandBuilder();
        $builder->setBinary('foo');
        $builder->clear();

        $builder->getCommand();
    }

    public function testClearTwo()
    {
        $builder = new ShellCommandBuilder();

        $builder->addParameter('test');

        $builder->clear();
        $builder->setBinary('./tests/data/empty_binary');

        $command = $builder->getCommand();

        $this->assertEquals(
            getcwd() . '/tests/data/empty_binary',
            $command->__toString()
        );
    }
}
