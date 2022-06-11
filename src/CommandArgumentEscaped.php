<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandArgumentInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;

/**
 * Value object representing a command argument requiring escaping.
 */
final class CommandArgumentEscaped implements CommandArgumentInterface
{
    public function __construct(
        private readonly string $argument,
        private readonly EnvironmentInterface $environment
    ) {
    }

    public function encode(): string
    {
        return $this->environment->escapeShellArg($this->argument);
    }
}
