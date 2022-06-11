<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test\Unit;

use ptlis\ShellCommand\Test\PtlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use RuntimeException;

/**
 * @covers \ptlis\ShellCommand\UnixEnvironment
 */
class UnixEnvironmentTest extends PtlisShellCommandTestcase
{
    public function testGetSupportedList(): void
    {
        $environment = new UnixEnvironment();

        $this->assertSame(
            ['Linux', 'Darwin'],
            $environment->getSupportedList()
        );
    }

    public function testEscapeShellArg(): void
    {
        $environment = new UnixEnvironment();

        $this->assertSame(
            '\'--foo=bar\'',
            $environment->escapeShellArg('--foo=bar')
        );
    }

    public function testGetCwdSuccess(): void
    {
        $environment = new UnixEnvironment();

        $this->assertSame(
            (string)\getcwd(),
            $environment->getNormalizedCwd()
        );
    }

    public function testGetCwdError(): void
    {
        global $mockGetcwd;
        $mockGetcwd = true;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to determine current working directory');

        $environment = new UnixEnvironment();
        $environment->getNormalizedCwd();

        $mockGetcwd = false;
    }
}
