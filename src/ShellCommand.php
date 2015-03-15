<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;

/**
 * Shell Command, encapsulates the data required to execute a shell command.
 */
class ShellCommand implements CommandInterface
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
     * @var ProcessObserverInterface|null
     */
    private $processObserver;


    /**
     * Constructor
     *
     * @param EnvironmentInterface $environment
     * @param string $command
     * @param string[] $argumentList
     * @param string $cwd
     * @param int $timeout
     * @param int $pollTimeout
     * @param ProcessObserverInterface|null $processObserver
     */
    public function __construct(
        EnvironmentInterface $environment,
        $command,
        array $argumentList,
        $cwd,
        $timeout = -1,
        $pollTimeout = 1000,
        ProcessObserverInterface $processObserver = null
    ) {
        $this->environment = $environment;
        $this->command = $command;
        $this->argumentList = $argumentList;
        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
        $this->cwd = $cwd;
        $this->processObserver = $processObserver;
    }

    /**
     * {@inheritDoc}
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

        return new ShellResult(
            $process->getExitCode(),
            $fullStdOut,
            $fullStdErr
        );
    }

    /**
     * {@inheritDoc}
     */
    public function runAsynchronous()
    {
        return $this->environment->buildProcess(
            $this,
            $this->cwd,
            $this->processObserver,
            $this->timeout,
            $this->pollTimeout
        );
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return array_reduce(
            $this->argumentList,
            function ($string, $argument) {
                return $string . ' ' . escapeshellarg($argument);
            },
            $this->command
        );
    }
}
