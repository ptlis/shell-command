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


use ptlis\ShellCommand\WindowsEnvironment;

class WindowsEnvironmentTest extends \PHPUnit_Framework_TestCase
{
    public function testFullyQualified()
    {
        $command = __DIR__ . '/../../commands/windows/test.bat';

        $env = new WindowsEnvironment();

        if (!in_array(PHP_OS, $env->getSupportedList())) {
            $this->markTestSkipped('Tests requires Windows operating system');
        }

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }
}
