<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Arguments;

use ptlis\ShellCommand\Argument\AdHoc;

class AdHocTest extends \PHPUnit_Framework_TestCase
{
    public function testAdHoc()
    {
        $adHoc = new AdHoc('foo=bar');

        $this->assertEquals('foo=bar', $adHoc->__toString());
    }
}
