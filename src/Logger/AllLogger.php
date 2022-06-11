<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Logs all information about the process.
 */
final class AllLogger implements ProcessObserverInterface
{
    private LoggerInterface $logger;
    private string $logLevel;

    public function __construct(
        LoggerInterface $logger,
        string $logLevel = LogLevel::DEBUG
    ) {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    public function processCreated(int $pid, string $command, string $cwd, array $environmentVariables): void
    {
        $this->log(
            'Process created',
            [
                'pid' => $pid,
                'command' => $command,
                'cwd' => $cwd,
                'env_vars' => $environmentVariables
            ]
        );
    }

    public function processPolled(int $pid, int $runningTime): void
    {
        $this->log('Process polled', ['pid' => $pid, 'running time' => $runningTime]);
    }

    public function stdOutRead(int $pid, string $stdOut): void
    {
        $this->log('Read from stdout', ['pid' => $pid, 'stdout' => $stdOut]);
    }

    public function stdErrRead(int $pid, string $stdErr): void
    {
        $this->log('Read from stderr', ['pid' => $pid, 'stderr' => $stdErr]);
    }

    public function sentSignal(int $pid, string $signal): void
    {
        $this->log('Signal sent', ['pid' => $pid, 'signal' => $signal]);
    }

    public function processExited(int $pid, ProcessOutputInterface $processOutput): void
    {
        $this->log('Process exited', ['pid' => $pid, 'exit_code' => $processOutput->getExitCode()]);
    }

    /**
     * Create a log entry, utility method for derived classes.
     *
     * @param array<mixed> $context
     */
    private function log(string $message, array $context = []): void
    {
        $this->logger->log($this->logLevel, $message, $context);
    }
}
