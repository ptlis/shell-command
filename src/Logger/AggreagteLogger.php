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
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;

/**
 * Aggregates several loggers.
 */
class AggregateLogger extends BaseProcessLogger
{
    /**
     * @var ProcessObserverInterface[]
     */
    private $loggerList;


    /**
     * Constructor.
     *
     * @param ProcessObserverInterface[] $loggerList
     * @param LoggerInterface $logger
     * @param string $logLevel
     */
    public function __construct(array $loggerList, LoggerInterface $logger, $logLevel = LogLevel::DEBUG)
    {
        parent::__construct($logger, $logLevel);

        $this->loggerList = $loggerList;
    }

    /**
     * {@inheritDoc}
     */
    public function processCreated($command)
    {
        foreach ($this->loggerList as $logger) {
            $logger->processCreated($command);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stdOutRead($stdOut)
    {
        foreach ($this->loggerList as $logger) {
            $logger->stdOutRead($stdOut);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stdErrRead($stdErr)
    {
        foreach ($this->loggerList as $logger) {
            $logger->stdErrRead($stdErr);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sentSignal($signal)
    {
        foreach ($this->loggerList as $logger) {
            $logger->sentSignal($signal);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function processExited($exitCode)
    {
        foreach ($this->loggerList as $logger) {
            $logger->processExited($exitCode);
        }
    }
}
