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
 * Logs the executed command when process is started.
 */
class ProcessStartedLogger extends BaseProcessLogger
{
    /**
     * {@inheritDoc}
     */
    public function processCreated($command)
    {
        $this->log('Process created with command: ' . $command);
    }
}
