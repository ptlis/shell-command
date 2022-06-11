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

namespace ptlis\ShellCommand\Interfaces;

/**
 * Interface that classes wishing to observe changes in processes must implement.
 */
interface ProcessObserverInterface
{
    /**
     * The process has been created from the provided command.
     *
     * @param int $pid The process id of the running process.
     * @param string $command The command to execute.
     * @param string $cwd The current working directory that the process was created in.
     * @param string[] $environmentVariables Array of environment variables passed to the process.
     */
    public function processCreated(int $pid, string $command, string $cwd, array $environmentVariables): void;

    /**
     * The process has had its status polled.
     *
     * @param int $pid The process id of the running process.
     * @param int $runningTime How long the process has been running for in milliseconds
     */
    public function processPolled(int $pid, int $runningTime): void;

    /**
     * The contents of the stdout buffer have been read.
     *
     * @param int $pid The process id of the running process.
     * @param string $stdOut The data output to stdout.
     */
    public function stdOutRead(int $pid, string $stdOut): void;

    /**
     * The contents of the stderr buffer have been read.
     *
     * @param int $pid The process id of the running process.
     * @param string $stdErr The data output to stderr.
     */
    public function stdErrRead(int $pid, string $stdErr): void;

    /**
     * A signal has been sent to the process.
     *
     * @param int $pid The process id of the running process.
     * @param string $signal String representation of the signal (e.g. SIGKILL).
     */
    public function sentSignal(int $pid, string $signal): void;

    /**
     * Process has completed and the exit code is available.
     *
     * @param int $pid The process id of the running process.
     * @param ProcessOutputInterface $processOutput Object representing the state of the terminated process.
     */
    public function processExited(int $pid, ProcessOutputInterface $processOutput): void;
}
