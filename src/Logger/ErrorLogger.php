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

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Logs error data from the process (stderr and exit code if not 0).
 */
class ErrorLogger extends AggregateLogger
{
    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param string $logLevel
     */
    public function __construct(LoggerInterface $logger, $logLevel = LogLevel::ERROR)
    {
        parent::__construct(
            array(
                new StdErrReadLogger($logger, $logLevel),
                new ProcessExitedLogger($logger, $logLevel)
            ),
            $logger,
            $logLevel
        );
    }
}
