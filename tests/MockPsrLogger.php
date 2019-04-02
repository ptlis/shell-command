<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Test;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class MockPsrLogger implements LoggerInterface
{
    /**
     * @var array Logs created.
     */
    private $logs = [];

    /**
     * @var int Fake process ID to use when logging
     */
    private $fakePid;

    /**
     * @param int $fakePid Fake process ID to use when logging
     */
    public function __construct($fakePid = 999)
    {
        $this->fakePid = $fakePid;
    }


    /**
     * {@inheritDoc}
     */
    public function emergency($message, array $context = [])
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
    public function alert($message, array $context = [])
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
    public function critical($message, array $context = [])
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
    public function error($message, array $context = [])
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
    public function warning($message, array $context = [])
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
    public function notice($message, array $context = [])
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
    public function info($message, array $context = [])
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
    public function debug($message, array $context = [])
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
    public function log($level, $message, array $context = [])
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
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }
}
