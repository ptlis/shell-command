<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Stores the output of a terminated process.
 */
final class ProcessOutput implements ProcessOutputInterface
{
    /** @var int */
    private $exitCode;

    /** @var string */
    private $stdOut;

    /** @var string */
    private $stdErr;

    /** @var string */
    private $command;


    public function __construct(
        int $exitCode,
        string $stdOut,
        string $stdErr,
        string $command
    ) {
        $this->exitCode = $exitCode;
        $this->stdOut = $stdOut;
        $this->stdErr = $stdErr;
        $this->command = $command;
    }

    public function getStdOut(): string
    {
        return $this->stdOut;
    }

    public function getStdOutLines(): array
    {
        return $this->stringToArray($this->stdOut);
    }

    public function getStdErr(): string
    {
        return $this->stdErr;
    }

    public function getStdErrLines(): array
    {
        return $this->stringToArray($this->stdErr);
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function getExecutedCommand(): string
    {
        return $this->command;
    }

    /**
     * Accepts console output as a string and returns an array of it split by newlines.
     */
    private function stringToArray(string $string): array
    {
        $lines = preg_split('/\R/', $string);
        if (1 === count($lines) && '' === $lines[0]) {
            $lines = [];
        }

        return $lines;
    }
}
