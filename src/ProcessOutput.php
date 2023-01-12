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
    use TriggerDeprecationTrait;

    /** @var array<string> */
    public readonly array $stdOutLines;
    /** @var array<string> */
    public readonly array $stdErrLines;

    public function __construct(
        public readonly int $exitCode,
        public readonly string $stdOut,
        public readonly string $stdErr,
        public readonly string $command,
        public readonly string $workingDirectory
    ) {
        $this->stdOutLines = $this->stringToArray($stdOut);
        $this->stdErrLines = $this->stringToArray($stdErr);
    }

    /**
     * @deprecated Replaced with direct access to the stdOut property.
     */
    public function getStdOut(): string
    {
        $this->triggerDeprecationWarning(__METHOD__, 'stdOut');
        return $this->stdOut;
    }

    /**
     * @deprecated Replaced with direct access to the stdOutLines property.
     */
    public function getStdOutLines(): array
    {
        $this->triggerDeprecationWarning(__METHOD__, 'stdOutLines');
        return $this->stdOutLines;
    }

    /**
     * @deprecated Replaced with direct access to the stdErr property.
     */
    public function getStdErr(): string
    {
        $this->triggerDeprecationWarning(__METHOD__, 'stdErr');
        return $this->stdErr;
    }

    /**
     * @deprecated Replaced with direct access to the stdErrLines property.
     */
    public function getStdErrLines(): array
    {
        $this->triggerDeprecationWarning(__METHOD__, 'stdErrLines');
        return $this->stdErrLines;
    }

    /**
     * @deprecated Replaced with direct access to the exitCode property.
     */
    public function getExitCode(): int
    {
        $this->triggerDeprecationWarning(__METHOD__, 'exitCode');
        return $this->exitCode;
    }

    /**
     * @deprecated Replaced with direct access to the command property.
     */
    public function getExecutedCommand(): string
    {
        $this->triggerDeprecationWarning(__METHOD__, 'command');
        return $this->command;
    }

    /**
     * @deprecated Replaced with direct access to the workingDirectory property.
     */
    public function getWorkingDirectory(): string
    {
        $this->triggerDeprecationWarning(__METHOD__, 'workingDirectory');
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
