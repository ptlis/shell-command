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
    public function processCreated($pid, $command)
    {
        foreach ($this->loggerList as $logger) {
            $logger->processCreated($pid, $command);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function processPolled($pid, $runningTime)
    {
        foreach ($this->loggerList as $logger) {
            $logger->processPolled($pid, $runningTime);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stdOutRead($pid, $stdOut)
    {
        foreach ($this->loggerList as $logger) {
            $logger->stdOutRead($pid, $stdOut);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stdErrRead($pid, $stdErr)
    {
        foreach ($this->loggerList as $logger) {
            $logger->stdErrRead($pid, $stdErr);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sentSignal($pid, $signal)
    {
        foreach ($this->loggerList as $logger) {
            $logger->sentSignal($pid, $signal);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function processExited($pid, $exitCode)
    {
        foreach ($this->loggerList as $logger) {
            $logger->processExited($pid, $exitCode);
        }
    }
}
