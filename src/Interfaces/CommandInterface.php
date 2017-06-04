<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Interfaces;

use GuzzleHttp\Promise\PromiseInterface;
use React\EventLoop\LoopInterface;

/**
 * Interface class that commands must implement.
 */
interface CommandInterface
{
    /**
     * Run the command blocking further execution, on completion return a result
     *
     * @return ProcessOutputInterface
     */
    public function runSynchronous();

    /**
     * Run the command asynchronously, return an object representing the process.
     *
     * @return ProcessInterface
     */
    public function runAsynchronous();

    /**
     * Returns a promise representing the result of the executed command.
     *
     * @param LoopInterface $eventLoop
     * @return PromiseInterface
     */
    public function runPromise(LoopInterface $eventLoop);

    /**
     * Gets the string representation of the command.
     *
     * @return string
     */
    public function __toString();
}
