<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit;

use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\UnixEnvironment
 */
class UnixEnvironmentTest extends ptlisShellCommandTestcase
{
    public function testGetSupportedList()
    {
        $environment = new UnixEnvironment();

        $this->assertSame(
            ['Linux', 'Darwin'],
            $environment->getSupportedList()
        );
    }

    public function testEscapeShellArg()
    {
        $environment = new UnixEnvironment();

        $this->assertSame(
            '\'--foo=bar\'',
            $environment->escapeShellArg('--foo=bar')
        );
    }

    public function testApplyEnvironmentVariables()
    {
        $environment = new UnixEnvironment();

        $this->assertSame(
            'FOO=\'bar\' BAZ=\'bat\' test',
            $environment->applyEnvironmentVariables('test', ['FOO' => 'bar', 'BAZ' => 'bat'])
        );
    }
}
