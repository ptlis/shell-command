<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Arguments;

use ptlis\ShellCommand\Flag;

class AdHocTest extends \PHPUnit_Framework_TestCase
{
    public function testFlagOnly()
    {
        $flag = new Flag('v');

        $this->assertEquals('-v', $flag->__toString());
    }

    public function testFlagAndValue()
    {
        $flag = new Flag('c', '/etc/foo.ini');

        $this->assertEquals('-c /etc/foo.ini', $flag->__toString());
    }
}
