<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandArgumentInterface;

/**
 * Value object representing a command argument that does not require escaping.
 */
final class CommandArgumentRaw implements CommandArgumentInterface
{
    public function __construct(private readonly string $argument)
    {
    }

    public function encode(): string
    {
        return $this->argument;
    }
}
