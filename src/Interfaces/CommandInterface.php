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
 * Interface class that commands must implement.
 */
interface CommandInterface
{
    /**
     * Run the command blocking further execution, on completion return a result
     *
     * @return CommandResultInterface
     */
    public function runSynchronous();
}
