<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Argument;

use ptlis\ShellCommand\Interfaces\ArgumentInterface;

/**
 * Parameter implementation, stores a parameter to pass.
 */
class Parameter implements ArgumentInterface
{
    /**
     * Constructor.
     *
     * @param string $parameter
     */
    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * Returns an argument to pass to a command.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->parameter;
    }
}
