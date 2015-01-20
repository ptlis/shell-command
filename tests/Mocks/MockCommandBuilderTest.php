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
use ptlis\ShellCommand\Mock\MockCommand;
use ptlis\ShellCommand\Mock\MockCommandBuilder;
use ptlis\ShellCommand\ShellResult;

class MockCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testMockCommandBuilderOne()
    {
        $builder = new MockCommandBuilder();

        $builder
            ->setBinary('foo')
            ->setExitCode(0)
            ->setOutput(array('hello world'))
            ->addArgument('foo', 'bar')
            ->addFlag('d', 10)
            ->addAdHoc('if=/bar')
            ->addParameter('wop');

        $builtCommand = $builder->getCommand();

        $this->assertEquals(
            new MockCommand(
                new MockBinary('foo'),
                array(
                    new Argument('foo', 'bar'),
                    new Flag('d', 10),
                    new AdHoc('if=/bar'),
                    new Parameter('wop')
                ),
                array('hello world'),
                0
            ),
            $builtCommand
        );

        $this->assertEquals(
            new ShellResult(0, array('hello world')),
            $builtCommand->run()
        );
    }

    public function testMockCommandBuilderTwo()
    {
        $builder = new MockCommandBuilder();

        $builder
            ->setBinary('bar')
            ->setExitCode(1)
            ->setOutput(array('hurray!'));

        $builtCommand = $builder->getCommand();

        $this->assertEquals(
            new MockCommand(
                new MockBinary('bar'),
                array(),
                array('hurray!'),
                1
            ),
            $builtCommand
        );

        $this->assertEquals(
            new ShellResult(1, array('hurray!')),
            $builtCommand->run()
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
}
