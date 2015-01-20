<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\ShellCommand;

use ptlis\ShellCommand\ShellResult;

class ShellResultTest extends \PHPUnit_Framework_TestCase
{
    public function testShellResult()
    {
        $shellResult = new ShellResult(
            0,
            array(
                'great success!'
            )
        );

        $this->assertSame(
            0,
            $shellResult->getExitCode()
        );

        $this->assertSame(
            array(
                'great success!'
            ),
            $shellResult->getOutput()
        );
    }
}
