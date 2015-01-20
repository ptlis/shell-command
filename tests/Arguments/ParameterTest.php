<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Arguments;

use ptlis\ShellCommand\Parameter;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    public function testFlagOnly()
    {
        $parameter = new Parameter('the_file');

        $this->assertEquals('the_file', $parameter->__toString());
    }
}
