<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\BinaryInterface;

/**
 * Mock implementation of the binary interface.
 */
class MockBinary implements BinaryInterface
{
    /**
     * @var string The binary to mock.
     */
    private $binary;


    /**
     * Constructor.
     *
     * @param string $binary
     */
    public function __construct($binary)
    {
        $this->binary = $binary;
    }

    /**
     * Return a string representation of fully-qualified path to the binary.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->binary;
    }
}
