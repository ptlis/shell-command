<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\Deferred;

/**
 * Shell Command, encapsulates the data required to execute a shell command.
 */
final class Command implements CommandInterface
{
    /**
     * @var EnvironmentInterface Instance of class that wraps environment-specific behaviours.
     */
    private $environment;

    /**
     * @var string The command to execute.
     */
    private $command;

    /**
     * @var string[] Array of arguments to pass with the command.
     */
    private $argumentList;

    /**
     * @var string[] Array of arguments to pass with the command without escaping.
     */
    private $rawArgumentList;

    /**
     * @var int (microseconds) How long to wait for a command to finish executing, -1 to wait indefinitely.
     */
    private $timeout;

    /**
     * @var int The amount of time in milliseconds to sleep for when polling for completion, defaults to 1/100 of a
     *  second.
     */
    private $pollTimeout;

    /**
     * @var string The current working directory to execute the command in.
     */
    private $cwd;

    /**
     * @var string[]
     */
    private $envVariableList;

    /**
     * @var ProcessObserverInterface
     */
    private $processObserver;


    /**
     * Constructor
     *
     * @param EnvironmentInterface $environment
     * @param ProcessObserverInterface $processObserver
     * @param string $command
     * @param string[] $argumentList
     * @param string[] $rawArgumentList
     * @param string $cwd
     * @param string[] $envVariableList
     * @param int $timeout
     * @param int $pollTimeout
     */
    public function __construct(
        EnvironmentInterface $environment,
        ProcessObserverInterface $processObserver,
        $command,
        array $argumentList,
        array $rawArgumentList,
        $cwd,
        $envVariableList = [],
        $timeout = -1,
        $pollTimeout = 1000
    ) {
        $this->environment = $environment;
        $this->processObserver = $processObserver;
        $this->command = $command;
        $this->argumentList = $argumentList;
        $this->rawArgumentList = $rawArgumentList;
        $this->timeout = $timeout;
        $this->envVariableList = $envVariableList;
        $this->pollTimeout = $pollTimeout;
        $this->cwd = $cwd;
    }

    /**
     * @inheritDoc
     */
    public function runSynchronous()
    {
        $process = $this->runAsynchronous();

        $fullStdOut = '';
        $fullStdErr = '';

        // Accumulate output as we wait
        $process->wait(function($stdOut, $stdErr) use (&$fullStdOut, &$fullStdErr) {
            $fullStdOut .= $stdOut;
            $fullStdErr .= $stdErr;
        });

        return new ProcessOutput(
            $process->getExitCode(),
            $fullStdOut,
            $fullStdErr
        );
    }

    /**
     * @inheritDoc
     */
    public function runAsynchronous()
    {
        return new Process(
            $this->environment,
            $this,
            $this->environment->expandPath($this->cwd),
            $this->timeout,
            $this->pollTimeout,
            $this->processObserver
        );
    }

    /**
     * @inheritDoc
     */
    public function runPromise(LoopInterface $eventLoop)
    {
        $fullStdOut = '';
        $fullStdErr = '';

        $deferred = new Deferred();

        // Delay beginning execution until the EventLoop::run method is invoked
        $eventLoop->addTimer(
            0,
            function() use ($eventLoop, $deferred, &$fullStdOut, &$fullStdErr) {

                $process = $this->runAsynchronous();

                // Poll the process periodically awaiting completion
                // TODO: Allow specification of poll time
                $eventLoop->addPeriodicTimer(
                    0.1,
                    function(TimerInterface $timer) use ($eventLoop, $deferred, $process, &$fullStdOut, &$fullStdErr) {
                        $fullStdOut .= $process->readOutput(ProcessInterface::STDOUT);
                        $fullStdErr .= $process->readOutput(ProcessInterface::STDERR);

                        // Process has terminated
                        if (!$process->isRunning()) {
                            $eventLoop->cancelTimer($timer);
                            $output = new ProcessOutput($process->getExitCode(), $fullStdOut, $fullStdErr);
                            $this->resolveOrRejectPromise($deferred, $output);
                        }
                    }
                );
            }
        );

        return $deferred->promise();
    }

    /**
     * Either resolve or reject the promise depending on the result of the mock operation.
     *
     * @param Deferred $deferred
     * @param ProcessOutputInterface $processOutput
     */
    private function resolveOrRejectPromise(
        Deferred $deferred,
        ProcessOutputInterface $processOutput
    ) {
        if (0 === $processOutput->getExitCode()) {
            $deferred->resolve($processOutput);
        } else {
            $deferred->reject($processOutput);
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $stringCommand = array_reduce(
            $this->argumentList,
            function ($string, $argument) {
                return $string . ' ' . $this->environment->escapeShellArg($argument);
            },
            $this->command
        );

        $stringCommand = array_reduce(
            $this->rawArgumentList,
            function ($string, $argument) {
                return $string . ' ' . $argument;
            },
            $stringCommand
        );

        return $this->environment->applyEnvironmentVariables($stringCommand, $this->envVariableList);
    }
}
