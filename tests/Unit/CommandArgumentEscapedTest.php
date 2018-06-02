<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit;

use ptlis\ShellCommand\CommandArgumentEscaped;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\CommandArgumentEscaped
 */
class CommandArgumentEscapedTest extends \PHPUnit_Framework_TestCase
{
    public function testEncode()
    {
        $argument = new CommandArgumentEscaped('foo bar', new UnixEnvironment());

        $this->assertSame(
            '\'foo bar\'',
            $argument->encode()
        );
    }
}