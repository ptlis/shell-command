<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Interfaces;

/**
 * Interface storing the fully-qualified path to a binary.
 */
interface BinaryInterface
{
    /**
     * Return a string representation of fully-qualified path to the binary.
     *
     * @return string
     */
    public function __toString();
}
