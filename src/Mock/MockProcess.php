<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;
use ptlis\ShellCommand\Process;

/**
 * Mock running process. Waits for the specified time before completing.
 */
class MockProcess implements ProcessInterface
{
    /** @var string */
    private $command;

    /** @var int */
    private $runFor;

    /** @var ProcessOutputInterface */
    private $result;

    /** @var int */
    private $pid;

    /** @var bool */
    private $stopped = false;

    /** @var int */
    private $startTime;


    /**
     * @param string $command
     * @param int $runFor How long to fake the process running for
     * @param ProcessOutputInterface $result
     * @param int $pid
     */
    public function __construct(
        $command,
        ProcessOutputInterface $result,
        $runFor = 1000,
        $pid = 123
    ) {
        $this->command = $command;
        $this->runFor = $runFor;
        $this->result = $result;
        $this->pid = $pid;
        $this->startTime = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning()
    {
        return (
            !$this->stopped
            && ((microtime(true) - $this->startTime) < ($this->runFor / 1000))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function wait(\Closure $callback = null)
    {
        while ($this->isRunning()) {
            usleep(50);
        }

        if ($callback) {
            $callback($this->result->getStdOut(), $this->result->getStdErr());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop($timeout = 1000000)
    {
        $this->stopped = true;
    }

    /**
     * {@inheritdoc}
     */
    public function readOutput($streamId)
    {
        $output = '';

        switch ($streamId) {
            case Process::STDOUT:
                $output = $this->result->getStdOut();
                break;

            case Process::STDERR:
                $output = $this->result->getStdErr();
                break;
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function sendSignal($signal)
    {
        // Can only be one of SIGTERM or SIGKILL, force process to stop
        $this->stopped = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getExitCode()
    {
        if ($this->isRunning()) {
            throw new \RuntimeException('Cannot get exit code from running process');
        }

        return $this->result->getExitCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getPid()
    {
        if (!$this->isRunning()) {
            throw new \RuntimeException('Stopped processed do not have a pid');
        }

        return $this->pid;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand()
    {
        return $this->command;
    }
}
