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
 * Logs reads from stdout.
 */
class StdErrReadLogger extends BaseProcessLogger
{
    /**
     * {@inheritDoc}
     */
    public function stdErrRead($stdErr)
    {
        $this->log('Read from stderr: ' . $stdErr);
    }
}
