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

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\ProcessOutput;

/**
 * Logs all information about the process.
 */
final class AllLogger implements ProcessObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $logLevel;


    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param string $logLevel
     */
    public function __construct(LoggerInterface $logger, $logLevel = LogLevel::DEBUG)
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    /**
     * Create a log entry, utility method for derived classes.
     *
     * @param string $message
     * @param array $context
     */
    protected function log($message, array $context = [])
    {
        $this->logger->log($this->logLevel, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function processCreated($pid, $command)
    {
        $this->log('Process created', ['pid' => $pid, 'command' => $command]);
    }

    /**
     * {@inheritDoc}
     */
    public function processPolled($pid, $runningTime)
    {
        $this->log('Process polled', ['pid' => $pid, 'running time' => $runningTime]);
    }

    /**
     * {@inheritDoc}
     */
    public function stdOutRead($pid, $stdOut)
    {
        $this->log('Read from stdout', ['pid' => $pid, 'stdout' => $stdOut]);
    }

    /**
     * {@inheritDoc}
     */
    public function stdErrRead($pid, $stdErr)
    {
        $this->log('Read from stderr', ['pid' => $pid, 'stderr' => $stdErr]);
    }

    /**
     * {@inheritDoc}
     */
    public function sentSignal($pid, $signal)
    {
        $this->log('Signal sent', ['pid' => $pid, 'signal' => $signal]);
    }

    /**
     * {@inheritDoc}
     */
    public function processExited($pid, ProcessOutput $processOutput)
    {
        $this->log('Process exited', ['pid' => $pid, 'exit_code' => $processOutput->getExitCode()]);
    }
}
