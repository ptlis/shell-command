<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\ProcessOutputInterface;

/**
 * Stores the output of a terminated process.
 */
final class ProcessOutput implements ProcessOutputInterface
{
    public function __construct(
        private readonly int $exitCode,
        private readonly string $stdOut,
        private readonly string $stdErr,
        private readonly string $command,
        private readonly string $workingDirectory
    ) {
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

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * Accepts console output as a string and returns an array of it split by newlines.
     *
     * @return array<string>
     */
    private function stringToArray(string $string): array
    {
        $lines = \preg_split('/\R/', $string);
        if (!\is_array($lines) || (1 === \count($lines) && '' === $lines[0])) {
            $lines = [];
        }
        return $lines;
    }
}
