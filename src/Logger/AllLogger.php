<?php

/**
 * @copyright (c) 2015-2017 brian ridley
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
     * The process has been created from the provided command.
     *
     * @param string $command
     */
    public function processCreated($command)
    {
        $this->log('Process created', ['command' => $command]);
    }

    /**
     * The contents of the stdout buffer have been read.
     *
     * @param string $stdOut
     */
    public function stdOutRead($stdOut)
    {
        $this->log('Read from stdout', ['stdout' => $stdOut]);
    }

    /**
     * The contents of the stderr buffer have been read.
     *
     * @param string $stdErr
     */
    public function stdErrRead($stdErr)
    {
        $this->log('Read from stderr', ['stderr' => $stdErr]);
    }

    /**
     * A signal has been sent to the process.
     *
     * @param int $signal
     */
    public function sentSignal($signal)
    {
        $this->log('Signal sent', ['signal' => $signal]);
    }

    /**
     * Process has completed and the exit code is available.
     *
     * @param int $exitCode
     */
    public function processExited($exitCode)
    {
        $this->log('Process exited', ['exit_code' => $exitCode]);
    }
}
