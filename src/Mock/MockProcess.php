<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;
use ptlis\ShellCommand\Process;
use ptlis\ShellCommand\ProcessOutput;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\Deferred;

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

    /** @var float */
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

        return $this->result;
    }

    /**
     * {@inheritdoc}
     */
    public function stop($timeout = 1000000)
    {
        $this->stopped = true;

        return $this->result;
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
    private function getExitCode()
    {
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

    /**
     * {@inheritdoc}
     */
    public function getPromise(LoopInterface $eventLoop)
    {
        $deferred = new Deferred();

        // Poll the process for completion
        $eventLoop->addPeriodicTimer(
            0.1,
            function (TimerInterface $timer) use ($eventLoop, $deferred, &$fullStdOut, &$fullStdErr) {
                $fullStdOut .= $this->readOutput(ProcessInterface::STDOUT);
                $fullStdErr .= $this->readOutput(ProcessInterface::STDERR);

                // Process has terminated
                if (!$this->isRunning()) {
                    $eventLoop->cancelTimer($timer);
                    $output = new ProcessOutput($this->getExitCode(), $fullStdOut, $fullStdErr);

                    // Resolve or reject promise
                    if (0 === $output->getExitCode()) {
                        $deferred->resolve($output);
                    } else {
                        $deferred->reject($output);
                    }
                }
            }
        );


        return $deferred->promise();
    }
}
