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
     * Constructor
     *
     * @param EnvironmentInterface $environment
     * @param string $command
     * @param string[] $argumentList
     * @param string $cwd
     * @param int $timeout
     * @param int $pollTimeout
     */
    public function __construct(
        EnvironmentInterface $environment,
        $command,
        array $argumentList,
        $cwd,
        $timeout = -1,
        $pollTimeout = 1000
    ) {
        $this->environment = $environment;
        $this->command = $command;
        $this->argumentList = $argumentList;
        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
        $this->cwd = $cwd;
    }

    /**
     * {@inheritDoc}
     */
    public function runSynchronous()
    {
        $process = $this->runAsynchronous();

        $process->wait();

        $exitCode = $process->getExitCode();
        $stdOut = $process->getCompleteOutput(UnixRunningProcess::STDOUT);
        $stdErr = $process->getCompleteOutput(UnixRunningProcess::STDERR);

        return new ShellResult(
            $exitCode,
            $stdOut,
            $stdErr
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
