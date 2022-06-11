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

namespace ptlis\ShellCommand\Test;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class MockPsrLogger implements LoggerInterface
{
    /**
     * @var array<mixed> Logs created.
     */
    private array $logs = [];

    /**
     * @var int Fake process ID to use when logging
     */
    private int $fakePid;

    /**
     * @param int $fakePid Fake process ID to use when logging
     */
    public function __construct(int $fakePid = 999)
    {
        $this->fakePid = $fakePid;
    }


    /**
     * {@inheritDoc}
     */
    public function emergency($message, array $context = []): void
    {
        $context['pid'] = $this->fakePid;
        $this->log(
            LogLevel::EMERGENCY,
            $message,
            $context
        );
    }

    /**
     * {@inheritDoc}
     */
    public function alert($message, array $context = []): void
    {
        $this->log(
            LogLevel::ALERT,
            $message,
            $context
        );
    }

    /**
     * {@inheritDoc}
     */
    public function critical($message, array $context = []): void
    {
        $this->log(
            LogLevel::CRITICAL,
            $message,
            $context
        );
    }

    /**
     * {@inheritDoc}
     */
    public function error($message, array $context = []): void
    {
        $this->log(
            LogLevel::ERROR,
            $message,
            $context
        );
    }

    /**
     * {@inheritDoc}
     */
    public function warning($message, array $context = []): void
    {
        $this->log(
            LogLevel::WARNING,
            $message,
            $context
        );
    }

    /**
     * {@inheritDoc}
     */
    public function notice($message, array $context = []): void
    {
        $this->log(
            LogLevel::NOTICE,
            $message,
            $context
        );
    }

    /**
     * {@inheritDoc}
     */
    public function info($message, array $context = []): void
    {
        $this->log(
            LogLevel::INFO,
            $message,
            $context
        );
    }

    /**
     * {@inheritDoc}
     */
    public function debug($message, array $context = []): void
    {
        $this->log(
            LogLevel::DEBUG,
            $message,
            $context
        );
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = []): void
    {
        if (array_key_exists('stderr', $context) && !strlen($context['stderr'])) {
            return;
        }

        if (array_key_exists('stdout', $context) && !strlen($context['stdout'])) {
            return;
        }

        $context['pid'] = $this->fakePid;

        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
    }

    /**
     * Get logged data.
     *
     * @return array<mixed>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
