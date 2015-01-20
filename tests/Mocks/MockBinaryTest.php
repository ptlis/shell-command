<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Mocks;

use ptlis\ShellCommand\Mock\MockBinary;

class MockBinaryTest extends \PHPUnit_Framework_TestCase
{
    public function testMockBinary()
    {
        $path = 'binary';

        $binary = new MockBinary($path);

        $this->assertSame($path, $binary->__toString());
    }
}
