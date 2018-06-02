<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit;

use ptlis\ShellCommand\CommandArgumentRaw;

/**
 * @covers \ptlis\ShellCommand\CommandArgumentRaw
 */
class CommandArgumentRawTest extends \PHPUnit_Framework_TestCase
{
    public function testEncode()
    {
        $argument = new CommandArgumentRaw('foo bar');

        $this->assertSame(
            'foo bar',
            $argument->encode()
        );
    }
}