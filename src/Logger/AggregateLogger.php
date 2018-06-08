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
 * Aggregates several loggers.
 */
final class AggregateLogger implements ProcessObserverInterface
{
    /**
     * @var ProcessObserverInterface[]
     */
    private $loggerList;


    /**
     * Constructor.
     *
     * @param ProcessObserverInterface[] $loggerList
     */
    public function __construct(array $loggerList)
    {
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
    public function processPolled($runningTime)
    {
        foreach ($this->loggerList as $logger) {
            $logger->processPolled($runningTime);
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
