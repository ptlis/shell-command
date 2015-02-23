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
 *
 * @todo Use timeout!
 */
class UnixRunningProcess implements RunningProcessInterface
{
    const STDIN = 0;
    const STDOUT = 1;
    const STDERR = 2;


    /**
     * @var int (microseconds) How long to wait for a command to finish executing, -1 to wait indefinitely.
     */
    private $timeout;

    /**
     * @var int (microseconds) The amount of time to sleep for when polling for completion, defaults to 1/100 of a
     *  second.
     */
    private $pollTimeout;

    /**
     * @var float Unix timestamp with microseconds.
     */
    private $startTime;

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
     * @var bool Whether to cache all output in this class.
     *
     * @todo Rethink this - is really about forcing the streams to be flushed regularly to prevent them filling &
     *  blocking the process.
     */
    private $cacheOutput;

    /**
     * @var string The accumulating output from STDOUT.
     */
    private $stdOut = '';

    /**
     * @var string The accumulating error output from STDOUT.
     */
    private $stdErr = '';

    /**
     * Constructor.
     *
     * @throws CommandExecutionException
     *
     * @param string $command
     * @param string $cwdOverride
     * @param int $timeout
     * @param int $pollTimeout
     * @param bool $cacheOutput
     */
    public function __construct($command, $cwdOverride, $timeout = -1, $pollTimeout = 1000, $cacheOutput = true)
    {
        $this->cacheOutput = $cacheOutput;

        // Store CWD, set to override
        $prevCwd = getcwd();
        chdir($cwdOverride);

        $this->process = proc_open(
            $command,
            array(
                self::STDOUT => array('pipe', 'w'),
                self::STDERR => array('pipe', 'w')
            ),
            $this->pipeList
        );
        $this->startTime = microtime(true);

        // Reset CWD to previous
        chdir($prevCwd);

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
        $status = $this->getStatus();

        return $status['running'];
    }

    /**
     * {@inheritDoc}
     */
    public function wait()
    {
        // TODO: We must make sure the buffer does not fill (or the process halts), so for now we're polling the output
        // every iteration of the loop. The problem is that this isn't the only way to use the class so other cases
        // will trigger this issue.
        while ($this->isRunning()) {
            $stdOut = $this->readOutput(self::STDOUT);
            $stdErr = $this->readOutput(self::STDERR);

            usleep($this->pollTimeout);
        }

        $stdOut = $this->readOutput(self::STDOUT);
        $stdErr = $this->readOutput(self::STDERR);

    }

    /**
     * {@inheritDoc}
     */
    public function stop($timeout = 1000000)
    {
        $originalTime = microtime(true);
        $this->sendSignal(SIGTERM);

        while ($this->isRunning()) {
            $time = microtime(true);

            // If term hasn't succeeded by the specified timeout then try and kill
            if (($time - $originalTime) * 1000000 > $timeout) {
                $this->sendSignal(SIGKILL);
                break;
            }

            usleep($this->pollTimeout);
        }
    }

    /**
     * Send a signal to the running process.
     *
     * @param int $signal
     */
    public function sendSignal($signal)
    {
        if (true !== proc_terminate($this->process, $signal)) {
            throw new CommandExecutionException(
                'Call to proc_terminate with signal "' . $signal . '" failed for unknown reason.'
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function readOutput($streamId)
    {
        $data = stream_get_contents($this->pipeList[$streamId]);
        if ($this->cacheOutput) {
            if (self::STDOUT === $streamId) {
                $this->stdOut .= $data;
            } elseif (self::STDERR === $streamId) {

                $this->stdErr .= $data;
            }
        }
        return $data;
    }

    /**
     * Read the complete output from the specified stream.
     *
     * @throws \RuntimeException If the process has not yet exited.
     *
     * @param int $streamId
     *
     * @return string
     */
    public function getCompleteOutput($streamId)
    {
        if ($this->isRunning()) {
            throw new \RuntimeException('Cannot get complete output of still-running process.');
        }

        $data = '';
        if (self::STDOUT === $streamId) {
            $data = $this->stdOut;
        } elseif (self::STDERR === $streamId) {
            $data = $this->stdErr;
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getExitCode()
    {
        if ($this->isRunning()) {
            throw new \RuntimeException('Cannot get exit code of still-running process.');
        }

        return $this->exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getPid()
    {
        $status = $this->getStatus();
        if (!$status['running']) {
            throw new \RuntimeException('Cannot get the process id of a process that has already exited.');
        }

        return $status['pid'];
    }

    /**
     * Proxy method to proc_get_status.
     *
     * This is used so that we always update the exit code when retrieving process status. This works around the issue
     * where only the last call after process termination contains the real exit code.
     *
     * See http://stackoverflow.com/a/7841550 For more information.
     *
     * @return array
     */
    private function getStatus()
    {
        $status = proc_get_status($this->process);

        if (!$status['running'] && is_null($this->exitCode)) {
            $this->exitCode = $status['exitcode'];
        }

        return $status;
    }
}
