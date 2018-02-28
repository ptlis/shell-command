<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Logger;

use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;

/**
 * Null logger, used when no observer is provided.
 */
class NullProcessObserver implements ProcessObserverInterface
{
    /**
     * The process has been created from the provided command.
     *
     * @param string $command
     */
    public function processCreated($command)
    {
        // Null observer, do nothing
    }

    /**
     * The contents of the stdout buffer have been read.
     *
     * @param string $stdOut
     */
    public function stdOutRead($stdOut)
    {
        // Null observer, do nothing
    }

    /**
     * The contents of the stderr buffer have been read.
     *
     * @param string $stdErr
     */
    public function stdErrRead($stdErr)
    {
        // Null observer, do nothing
    }

    /**
     * A signal has been sent to the process.
     *
     * @param int $signal
     */
    public function sentSignal($signal)
    {
        // Null observer, do nothing
    }

    /**
     * Process has completed and the exit code is available.
     *
     * @param int $exitCode
     */
    public function processExited($exitCode)
    {
        // Null observer, do nothing
    }
}
