<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @param string $command
     */
    public function processCreated($pid, $command);

    /**
     * The process has had it's status polled.
     *
     * @param int $pid The process id of the running process.
     * @param int $runningTime How long the process has been running for in milliseconds
     */
    public function processPolled($pid, $runningTime);

    /**
     * The contents of the stdout buffer have been read.
     *
     * @param int $pid The process id of the running process.
     * @param string $stdOut
     */
    public function stdOutRead($pid, $stdOut);

    /**
     * The contents of the stderr buffer have been read.
     *
     * @param int $pid The process id of the running process.
     * @param string $stdErr
     */
    public function stdErrRead($pid, $stdErr);

    /**
     * A signal has been sent to the process.
     *
     * @param int $pid The process id of the running process.
     * @param int $signal
     */
    public function sentSignal($pid, $signal);

    /**
     * Process has completed and the exit code is available.
     *
     * @param int $pid The process id of the running process.
     * @param int $exitCode
     */
    public function processExited($pid, $exitCode);
}
