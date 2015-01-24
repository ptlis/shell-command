<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Mocks;


use ptlis\ShellCommand\Argument\AdHoc;
use ptlis\ShellCommand\Argument\Argument;
use ptlis\ShellCommand\Argument\Flag;
use ptlis\ShellCommand\Argument\Parameter;
use ptlis\ShellCommand\Mock\MockBinary;
use ptlis\ShellCommand\Mock\MockSynchronousCommand;
use ptlis\ShellCommand\Mock\MockCommandBuilder;
use ptlis\ShellCommand\ShellResult;

class MockCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testMockCommandBuilderOne()
    {
        $builder = new MockCommandBuilder();

        $builder
            ->setBinary('foo')
            ->addMockResult(0, 'hello world', '')
            ->addArgument('foo', 'bar')
            ->addFlag('d', 10)
            ->addAdHoc('if=/bar')
            ->addParameter('wop');

        $builtCommand = $builder->getCommand();

        $expectCommand = new MockSynchronousCommand(
            new MockBinary('foo'),
            array(
                new Argument('foo', 'bar'),
                new Flag('d', 10),
                new AdHoc('if=/bar'),
                new Parameter('wop')
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
            $builtCommand->run()
        );
    }

    public function testMockCommandBuilderTwo()
    {
        $builder = new MockCommandBuilder();

        $builder
            ->setBinary('bar')
            ->addMockResult(1, 'hurray!', '');

        $builtCommand = $builder->getCommand();

        $expectCommand = new MockSynchronousCommand(
            new MockBinary('bar'),
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
            $builtCommand->run()
        );
    }

    public function testMockCommandMultiUse()
    {
        $builder = new MockCommandBuilder();

        $builder
            ->addMockResult(1, 'hurray!', '')
            ->addMockResult(0, 'success', '');

        // First
        $builder
            ->setBinary('bar');

        $builtCommand1 = $builder->getCommand();

        $expectResult1 = new ShellResult(1, 'hurray!', '');
        $expectCommand1 = new MockSynchronousCommand(
            new MockBinary('bar'),
            array(),
            new ShellResult(1, 'hurray!', '')
        );

        $this->assertEquals(
            $expectResult1,
            $builtCommand1->run()
        );

        $builder
            ->setBinary('baz');

        $builtCommand2 = $builder->getCommand();

        $expectResult2 = new ShellResult(0, 'success', '');
        $expectCommand2 = new MockSynchronousCommand(
            new MockBinary('baz'),
            array(),
            new ShellResult(0, 'success', '')
        );

        $this->assertEquals(
            $expectResult2,
            $builtCommand2->run()
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

        $builder->getCommand();
    }

    public function testClearOne()
    {
        $this->setExpectedException(
            '\RuntimeException',
            'No binary was provided to "ptlis\ShellCommand\Mock\MockCommandBuilder", unable to build command.'
        );
        $builder = new MockCommandBuilder();
        $builder->setBinary('foo');
        $builder->clear();

        $builder->getCommand();
    }

    public function testClearTwo()
    {
        $builder = new MockCommandBuilder();

        $builder->addParameter('test');

        $builder->clear();
        $builder->setBinary('foo');
        $builder->addMockResult(0, array('bar'), array());

        $command = $builder->getCommand();

        $this->assertEquals(
            'foo',
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

        $builder->setBinary('foo');
        $builder->addParameter('test');
        $builder->getCommand();
    }
}
