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
 * Basic argument implementation, accepts a flag & (optional) value.
 */
class Flag implements ArgumentInterface
{
    /**
     * @var string The flag to pass.
     */
    private $flag;

    /**
     * @var string (optional) value to pass along with flag.
     */
    private $value;


    /**
     * Constructor.
     *
     * @param string $flag
     * @param string $value
     */
    public function __construct($flag, $value = '')
    {
        $this->flag = $flag;
        $this->value = $value;
    }

    /**
     * Returns an argument to pass to a command.
     *
     * @return string
     */
    public function __toString()
    {
        $string = '-' . $this->flag;

        if (strlen($this->value)) {
            $string .= ' ' . $this->value;
        }

        return $string;
    }
}
