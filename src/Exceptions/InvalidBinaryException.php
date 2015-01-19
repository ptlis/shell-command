<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Exceptions;

/**
 * Exception thrown when a binary is not valid.
 */
class InvalidBinaryException extends \LogicException
{
    /**
     * Constructor.
     *
     * @param string $binary
     */
    public function __construct($binary)
    {
        $this->message = 'Binary file "' . $binary . '" not found or not executable.';
    }
}
