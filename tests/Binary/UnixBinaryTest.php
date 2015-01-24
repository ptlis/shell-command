<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Binary;

use ptlis\ShellCommand\UnixBinary;

class UnixBinaryTest extends \PHPUnit_Framework_TestCase
{
    public function testFullyQualified()
    {
        $path = __DIR__ . '/../data/empty_binary';

        $binary = new UnixBinary($path);

        $this->assertSame(realpath($path), $binary->__toString());
    }

    public function testGlobal()
    {
        $oldBinaryPath = getenv('PATH');

        $pathToBinary = realpath(getcwd() . '/tests/data');

        putenv('PATH=/foo/bar:/baz/bat:' . $pathToBinary);

        $binary = new UnixBinary('empty_binary');

        $this->assertSame(realpath($pathToBinary . '/empty_binary'), $binary->__toString());

        putenv('PATH=' . $oldBinaryPath);
    }

    public function testRelative()
    {
        $path = './tests/data/empty_binary';

        $binary = new UnixBinary($path);

        $this->assertSame(realpath($path), $binary->__toString());
    }

    public function testNotFound()
    {
        $this->setExpectedException(
            '\ptlis\ShellCommand\Exceptions\InvalidBinaryException',
            'Binary file "wibble_1_2_3" not found or not executable.'
        );

        new UnixBinary('wibble_1_2_3');
    }
}
