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
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Logs error data from the process (stderr and exit code if not 0).
 */
final class ErrorLogger extends NullProcessObserver
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $logLevel = LogLevel::ERROR
    ) {
    }

    public function stdErrRead(int $pid, string $stdErr): void
    {
        $this->log('Read from stderr', ['pid' => $pid, 'stderr' => $stdErr]);
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
