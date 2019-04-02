<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandArgumentInterface;

/**
 * Value object representing a command argument that does not require escaping.
 */
final class CommandArgumentRaw implements CommandArgumentInterface
{
    /**
     * @var string
     */
    private $argument;

    /**
     * @param string $argument
     */
    public function __construct(
        $argument
    ) {
        $this->argument = $argument;
    }

    /**
     * @inheritDoc
     */
    public function encode()
    {
        return $this->argument;
    }
}
