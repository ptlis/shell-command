<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\ArgumentInterface;

/**
 * Ad-hoc argument, useful for situations where the command is very old and/or behaves oddly (eg dd).
 *
 */
class AdHocArgument implements ArgumentInterface
{
    /**
     * @var string Ad-hoc argument to pass to a command.
     */
    private $argument;


    /**
     * Constructor.
     *
     * @param string $argument
     */
    public function __construct($argument)
    {
        $this->argument = $argument;
    }

    /**
     * Returns an argument to pass to a command.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->argument;
    }
}
