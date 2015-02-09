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
use ptlis\ShellCommand\Interfaces\SynchronousCommandInterface;
use ptlis\ShellCommand\Interfaces\CommandResultInterface;

/**
 * Shell Command, encapsulates the data required to execute a synchronous shell command.
 */
class ShellSynchronousCommand implements SynchronousCommandInterface
{
    const STDIN_INDEX = 0;
    const STDOUT_INDEX = 1;
    const STDERR_INDEX = 2;
    const EXITCODE_INDEX = 3;

    /**
     * @var ProcessInterface Object through which the command is executed.
     */
    private $process;

    /**
     * @var string The command to execute.
     */
    private $binary;

    /**
     * @var string[] Array of arguments to pass with the command.
     */
    private $argumentList;


    /**
     * Constructor
     *
     * @param ProcessInterface $process
     * @param string $binary
     * @param string[] $argumentList
     */
    public function __construct(
        ProcessInterface $process,
        $binary,
        array $argumentList
    ) {
        $this->process = $process;
        $this->binary = $binary;
        $this->argumentList = $argumentList;
    }

    /**
     * Execute the command and return its result.
     *
     * @throws CommandExecutionException
     *
     * @return CommandResultInterface
     */
    public function run()
    {
        return $this->process->runSynchronous(strval($this));
    }

    /**
     * Gets the string representation of the command, ready to execute.
     *
     * @return string
     */
    public function __toString()
    {
        return array_reduce(
            $this->argumentList,
            function ($string, $argument) {
                return $string . ' ' . escapeshellarg($argument);
            },
            $this->binary
        );
    }
}
