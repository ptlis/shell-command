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
use ptlis\ShellCommand\Interfaces\ArgumentInterface;
use ptlis\ShellCommand\Interfaces\BinaryInterface;
use ptlis\ShellCommand\Interfaces\SynchronousCommandInterface;
use ptlis\ShellCommand\Interfaces\CommandResultInterface;

/**
 * Shell Command, encapsulates the data required to execute a shell command.
 */
class ShellSynchronousCommand implements SynchronousCommandInterface
{
    const STDIN_INDEX = 0;
    const STDOUT_INDEX = 1;
    const STDERR_INDEX = 2;
    const EXITCODE_INDEX = 3;

    /**
     * @var BinaryInterface The binary to execute.
     */
    private $binary;

    /**
     * @var ArgumentInterface[] Array of arguments to pass with the command.
     */
    private $argumentList;


    /**
     * Constructor
     *
     * @param BinaryInterface $binary
     * @param ArgumentInterface[] $argumentList
     */
    public function __construct(BinaryInterface $binary, array $argumentList)
    {
        $this->binary = $binary;
        $this->argumentList = $argumentList;
    }

    /**
     * Execute the command and return its result.
     *
     * Note: We use a hack to ensure we get the correct status code - it seems like there is a long-standing issue with
     * the one provided by proc_close on some systems, see this php.net comment for more details:
     *    http://php.net/manual/en/function.proc-close.php#56798
     *
     * @throws CommandExecutionException
     *
     * @return CommandResultInterface
     */
    public function run()
    {
        $process = proc_open(
            $this . '; echo $? >&3',
            array(
                self::STDOUT_INDEX => array('pipe', 'w'),
                self::STDERR_INDEX => array('pipe', 'w'),
                self::EXITCODE_INDEX => array('pipe', 'w')
            ),
            $pipes
        );

        if (!is_resource($process)) {
            throw new CommandExecutionException('Call to proc_open failed for unknown reason.');
        }

        $stdOut = stream_get_contents($pipes[self::STDOUT_INDEX]);
        $stdErr = stream_get_contents($pipes[self::STDERR_INDEX]);
        $exitCode = trim(stream_get_contents($pipes[self::EXITCODE_INDEX]));

        proc_close($process);

        $trimChars = "\n\r";
        return new ShellResult(
            intval($exitCode),
            trim($stdOut, $trimChars),
            trim($stdErr, $trimChars)
        );
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
            $this->binary->__toString()
        );
    }
}
