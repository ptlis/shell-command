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

use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Null logger, used when no observer is provided.
 */
class NullProcessObserver implements ProcessObserverInterface
{
    public function processCreated(int $pid, string $command, string $cwd, array $environmentVariables): void
    {
        // Null observer, do nothing
    }

    public function processPolled(int $pid, int $runningTime): void
    {
        // Null observer, do nothing
    }

    public function stdOutRead(int $pid, string $stdOut): void
    {
        // Null observer, do nothing
    }

    public function stdErrRead(int $pid, string $stdErr): void
    {
        // Null observer, do nothing
    }

    public function sentSignal(int $pid, string $signal): void
    {
        // Null observer, do nothing
    }

    public function processExited(int $pid, ProcessOutputInterface $processOutput): void
    {
        // Null observer, do nothing
    }
}
