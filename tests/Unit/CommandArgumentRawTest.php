<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Unit;

use PHPUnit\Framework\TestCase;
use ptlis\ShellCommand\CommandArgumentRaw;

/**
 * @covers \ptlis\ShellCommand\CommandArgumentRaw
 */
class CommandArgumentRawTest extends TestCase
{
    public function testEncode(): void
    {
        $argument = new CommandArgumentRaw('foo bar');

        $this->assertSame(
            'foo bar',
            $argument->encode()
        );
    }
}
