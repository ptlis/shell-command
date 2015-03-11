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

namespace ptlis\ShellCommand\Logger;

/**
 * Logs sent signals
 */
class SignalSentLogger extends BaseProcessLogger
{
    /**
     * {@inheritDoc}
     */
    public function sentSignal($signal)
    {
        $this->log('Signal Sent: ' . $signal);
    }
}
