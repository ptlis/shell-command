<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Mock;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\CommandResultInterface;

class MockCommand implements CommandInterface
{
    /**
     * @var string The command to execute.
     */
    private $command;

    /**
     * @var string[] Array of arguments to pass with the command.
     */
    private $argumentList;

    /**
     * @var CommandResultInterface The mocked result of this operation.
     */
    private $result;


    /**
     * Constructor
     *
     * @param string $command
     * @param string[] $argumentList
     * @param CommandResultInterface $result
     */
    public function __construct(
        $command,
        array $argumentList,
        CommandResultInterface $result
    ) {
        $this->command = $command;
        $this->argumentList = $argumentList;
        $this->result = $result;
    }

    /**
     * {@inheritDoc}
     */
    public function runSynchronous()
    {
        return $this->result;
    }

    /**
     * {@inheritDoc}
     */
    public function runAsynchronous()
    {
        // TODO: Implement runAsynchronous() method.
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
