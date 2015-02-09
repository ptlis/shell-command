<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Mocks;

use ptlis\ShellCommand\Mock\MockSynchronousCommand;
use ptlis\ShellCommand\ShellResult;

class MockCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testMockCommand()
    {
        $path = 'binary';

        $command = new MockSynchronousCommand(
            $path,
            array('foo'),
            new ShellResult(0, array('hello world'), '')
        );

        $this->assertEquals($path . ' \'foo\'', $command->__toString());

        $this->assertEquals(
            new ShellResult(0, array('hello world'), ''),
            $command->run()
        );
    }
}
