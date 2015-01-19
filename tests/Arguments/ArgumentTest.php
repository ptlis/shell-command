<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Arguments;

use ptlis\ShellCommand\Argument;

class ArgumentTest extends \PHPUnit_Framework_TestCase
{
    public function testArgumentOnly()
    {
        $argument = new Argument('foo');

        $this->assertEquals('--foo', $argument->__toString());
    }

    public function testArgumentAndValue()
    {
        $argument = new Argument('foo', 'bar');

        $this->assertEquals('--foo bar', $argument->__toString());
    }
}
