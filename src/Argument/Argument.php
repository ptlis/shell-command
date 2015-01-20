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
 * Basic argument implementation, accepts a key, (optional) value & separator
 */
class Argument implements ArgumentInterface
{
    /**
     * @var string The key portion of the argument
     */
    private $argument;

    /**
     * @var string The (optional) value portion of the argument
     */
    private $value;

    /**
     * @var string The (optional) separator between key & value when present.
     */
    private $separator;


    /**
     * Constructor.
     *
     * @param string $argument
     * @param string $value
     * @param string $separator
     */
    public function __construct($argument, $value = '', $separator = ArgumentInterface::SEPARATOR_SPACE)
    {
        $this->argument = $argument;
        $this->value = $value;
        $this->separator = $separator;
    }

    /**
     * Returns an argument to pass to a command.
     *
     * @return string
     */
    public function __toString()
    {
        $string = '--' . $this->argument;

        if (strlen($this->value)) {
            $string .= $this->separator . $this->value;
        }

        return $string;
    }
}
