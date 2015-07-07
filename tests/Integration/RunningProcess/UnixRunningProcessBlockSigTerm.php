<?php

/**
 * PHP Version 5.3
 *
 * @copyright   (c) 2015 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Test\Integration\RunningProcess;

use ptlis\ShellCommand\Interfaces\RunningProcessInterface;
use ptlis\ShellCommand\UnixRunningProcess;

/**
 * Simple class wrapping UnixRunningProcess & blocking the sigterm signal.
 */
class UnixRunningProcessBlockSigTerm extends UnixRunningProcess
{
    /**
     * {@inheritDoc}
     */
    public function sendSignal($signal)
    {
        // Only send signal when not sigterm.
        if (RunningProcessInterface::SIGTERM !== $signal) {
            parent::sendSignal($signal);
        }
    }
}
