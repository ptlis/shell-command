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
 * Interface class representing a single argument passed to a command.
 */
interface ArgumentInterface
{
    /** Equals character separator between argument & value */
    const SEPARATOR_EQUALS = '=';

    /** Space character separator between argument & value. */
    const SEPARATOR_SPACE = ' ';


    /**
     * Returns an argument to pass to a command.
     *
     * @return string
     */
    public function __toString();
}
