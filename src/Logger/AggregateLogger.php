<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Logger;

use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Aggregates several loggers.
 */
final class AggregateLogger implements ProcessObserverInterface
{
    /** @var ProcessObserverInterface[] */
    private $loggerList;


    public function __construct(array $loggerList)
    {
        $this->loggerList = $loggerList;
    }

    public function processCreated(int $pid, string $command, string $cwd, array $environmentVariables): void
    {
        foreach ($this->loggerList as $logger) {
            $logger->processCreated($pid, $command, $cwd, $environmentVariables);
        }
    }

    public function processPolled(int $pid, int $runningTime): void
    {
        foreach ($this->loggerList as $logger) {
            $logger->processPolled($pid, $runningTime);
        }
    }
    public function stdOutRead(int $pid, string $stdOut): void
    {
        foreach ($this->loggerList as $logger) {
            $logger->stdOutRead($pid, $stdOut);
        }
    }

    public function stdErrRead(int $pid, string $stdErr): void
    {
        foreach ($this->loggerList as $logger) {
            $logger->stdErrRead($pid, $stdErr);
        }
    }

    public function sentSignal(int $pid, string $signal): void
    {
        foreach ($this->loggerList as $logger) {
            $logger->sentSignal($pid, $signal);
        }
    }

    public function processExited(int $pid, ProcessOutputInterface $processOutput): void
    {
        foreach ($this->loggerList as $logger) {
            $logger->processExited($pid, $processOutput);
        }
    }
}
