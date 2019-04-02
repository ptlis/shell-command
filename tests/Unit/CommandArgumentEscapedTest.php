<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit;

use PHPUnit\Framework\TestCase;
use ptlis\ShellCommand\CommandArgumentEscaped;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\CommandArgumentEscaped
 */
class CommandArgumentEscapedTest extends TestCase
{
    public function testEncode(): void
    {
        $argument = new CommandArgumentEscaped('foo bar', new UnixEnvironment());

        $this->assertSame(
            '\'foo bar\'',
            $argument->encode()
        );
    }
}
