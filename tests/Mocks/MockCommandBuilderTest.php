<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Mocks;


use ptlis\ShellCommand\Mock\MockCommand;
use ptlis\ShellCommand\Mock\MockCommandBuilder;
use ptlis\ShellCommand\ShellResult;

class MockCommandBuilderTest extends \PHPUnit_Framework_TestCase
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
            'foo',
            array(
                '--foo bar',
                '-d 10',
                'if=/bar',
                'wop'
            ),
            new ShellResult(0, 'hello world', '')
        );

        $this->assertEquals(
            $expectCommand,
            $builtCommand
        );

        $this->assertEquals(
            array($expectCommand),
            $builder->getBuiltCommands()
        );

        $this->assertEquals(
            new ShellResult(0, 'hello world', ''),
            $builtCommand->runSynchronous()
        );
    }

    public function testMockCommandBuilderTwo()
    {
        $builder = new MockCommandBuilder();

        $builtCommand = $builder
            ->setCommand('bar')
            ->addMockResult(1, 'hurray!', '')
            ->buildCommand();

        $expectCommand = new MockCommand(
            'bar',
            array(),
            new ShellResult(1, 'hurray!', '')
        );

        $this->assertEquals(
            $expectCommand,
            $builtCommand
        );

        $this->assertEquals(
            array($expectCommand),
            $builder->getBuiltCommands()
        );

        $this->assertEquals(
            new ShellResult(1, 'hurray!', ''),
            $builtCommand->runSynchronous()
        );
    }

    public function testMockCommandMultiUse()
    {
        $builder = new MockCommandBuilder();

        $builtCommand1 = $builder
            ->addMockResult(1, 'hurray!', '')
            ->addMockResult(0, 'success', '')
            ->setCommand('bar')
            ->buildCommand();

        $expectResult1 = new ShellResult(1, 'hurray!', '');
        $expectCommand1 = new MockCommand(
            'bar',
            array(),
            new ShellResult(1, 'hurray!', '')
        );

        $this->assertEquals(
            $expectResult1,
            $builtCommand1->runSynchronous()
        );

        $builtCommand2 = $builder
            ->setCommand('baz')
            ->addMockResult(0, 'success', '')
            ->buildCommand();

        $expectResult2 = new ShellResult(0, 'success', '');
        $expectCommand2 = new MockCommand(
            'baz',
            array(),
            new ShellResult(0, 'success', '')
        );

        $this->assertEquals(
            $expectResult2,
            $builtCommand2->runSynchronous()
        );

        $this->assertEquals(
            array($expectCommand1, $expectCommand2),
            $builder->getBuiltCommands()
        );
    }

    public function testInvalidBinary()
    {
        $this->setExpectedException(
            '\RuntimeException',
            'No binary was provided to "ptlis\ShellCommand\Mock\MockCommandBuilder", unable to build command.'
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
            ->addMockResult(0, array('bar'), array())
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
