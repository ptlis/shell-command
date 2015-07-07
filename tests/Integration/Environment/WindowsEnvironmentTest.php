<?php

/**
 * PHP Version 5.3
 *
 * @copyright   (c) 2015 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Test\Integration\Environment;

use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\WindowsEnvironment;

class WindowsEnvironmentTest extends ptlisShellCommandTestcase
{
    public function testFullyQualified()
    {
        $this->skipIfNotWindows();

        $command = __DIR__ . '\..\..\commands\windows\test.bat';

        $env = new WindowsEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }

    public function testRelative()
    {
        $this->skipIfNotWindows();

        $command = 'tests\commands\windows\test.bat';

        $env = new WindowsEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }
}
