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
use ptlis\ShellCommand\Interfaces\ProcessInterface;

/**
 * Provides shared functionality for synchronous & asynchronous commands.
 */
class UnixProcess implements ProcessInterface
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
     * @param int $pollTimeout
     */
    public function __construct($pollTimeout = 1000)
    {
        $this->pollTimeout = $pollTimeout;
    }

    /**
     * {@inheritDoc}
     *
     * Note: We use a hack to ensure we get the correct status code - it seems like there is a long-standing issue with
     * the one provided by proc_close on some systems, see this php.net comment for more details:
     *    http://php.net/manual/en/function.proc-close.php#56798
     */
    public function start($command)
    {
        if ($this->isRunning()) {
            throw new CommandExecutionException('Unable to start process - another process is already executing.');
        }

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
    }

    /**
     * {@inheritDoc}
     */
    public function isRunning()
    {
        $running = false;
        if (is_resource($this->process)) {
            $status = proc_get_status($this->process);

            $running = $status['running'];
        }

        return $running;
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
    public function runSynchronous($command)
    {
        $this->start($command);

        $this->wait();

        $exitCode = intval($this->readPipe(self::EXITCODE));
        $stdOut = $this->readPipe(self::STDOUT);
        $stdErr = $this->readPipe(self::STDERR);

        $this->resetState();

        return new ShellResult(
            $exitCode,
            $stdOut,
            $stdErr
        );
    }

    /**
     * Read from the specified pipe, removing trailing newlines.
     *
     * @param int $index One of class constants (STDIN, STDOUT, STDERR, EXITCODE).
     *
     * @return string.
     */
    private function readPipe($index)
    {
        return rtrim(
            stream_get_contents($this->pipeList[$index]),
            "\r\n"
        );
    }

    /**
     * Resets the stateful components of this process ready for another execution.
     */
    private function resetState()
    {
        if (is_resource($this->process)) {
            foreach ($this->pipeList as $pipe) {
                fclose($pipe);
            }

            proc_close($this->process);
        }

        $this->process = null;
        $this->pipeList = array();
    }
}
