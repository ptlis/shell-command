<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Exceptions\CommandExecutionException;
use ptlis\ShellCommand\Interfaces\RunningProcessInterface;

/**
 * UNIX implementation of running process.
 */
class UnixRunningProcess implements RunningProcessInterface
{
    const STDIN = 0;
    const STDOUT = 1;
    const STDERR = 2;
    const EXITCODE = 3;

    /**
     * @var int The amount of time in milliseconds to sleep for when polling for completion, defaults to 1/100 of a
     *  second.
     */
    private $pollTimeout;

    /**
     * @var int The exit code of the process, set once the process has exited.
     */
    private $exitCode;

    /**
     * @var array Pipes populated by proc_open.
     */
    private $pipeList = array();

    /**
     * @var resource Process resource returned by proc_open.
     */
    private $process = null;


    /**
     * Constructor.
     *
     * @throws CommandExecutionException
     *
     * @param string $command
     * @param int $pollTimeout
     */
    public function __construct($command, $pollTimeout = 1000)
    {
        $this->process = proc_open(
            $command . '; echo $? >&3',
            array(
                self::STDOUT => array('pipe', 'w'),
                self::STDERR => array('pipe', 'w'),
                self::EXITCODE => array('pipe', 'w')
            ),
            $this->pipeList
        );

        if (!is_resource($this->process)) {
            throw new CommandExecutionException('Call to proc_open failed for unknown reason.');
        }

        $this->pollTimeout = $pollTimeout;
    }

    /**
     * {@inheritDoc}
     */
    public function isRunning()
    {
        $status = proc_get_status($this->process);

        return $status['running'];
    }

    /**
     * {@inheritDoc}
     */
    public function wait()
    {
        while ($this->isRunning()) {
            usleep($this->pollTimeout);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stop()
    {
        // TODO: Implement stop() method.
    }

    /**
     * {@inheritDoc}
     */
    public function readOutput($streamId)
    {
        return stream_get_contents($this->pipeList[$streamId]);
    }

    /**
     * {@inheritDoc}
     */
    public function getExitCode()
    {
        if ($this->isRunning()) {
            throw new \RuntimeException('Cannot get exit code of still-running process.');
        }

        if (is_null($this->exitCode)) {
            $this->exitCode = intval($this->readOutput(self::EXITCODE));
        }

        return $this->exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getPid()
    {
        $status = proc_get_status($this->process);
        if (!$status['running']) {
            throw new \RuntimeException('Cannot get the process id of a process that has already exited.');
        }

        return $status['pid'];
    }
}
