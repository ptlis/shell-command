<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Logger;

use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Null logger, used when no observer is provided.
 */
class NullProcessObserver implements ProcessObserverInterface
{
    /**
     * {@inheritDoc}
     */
    public function processCreated($pid, $command)
    {
        // Null observer, do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function processPolled($pid, $runningTime)
    {
        // Null observer, do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function stdOutRead($pid, $stdOut)
    {
        // Null observer, do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function stdErrRead($pid, $stdErr)
    {
        // Null observer, do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function sentSignal($pid, $signal)
    {
        // Null observer, do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function processExited($pid, ProcessOutputInterface $processOutput)
    {
        // Null observer, do nothing
    }
}
