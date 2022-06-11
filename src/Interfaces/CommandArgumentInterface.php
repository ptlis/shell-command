<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Interfaces;

/**
 * Value object representing a command argument.
 */
interface CommandArgumentInterface
{
    /**
     * Encodes the argument for inclusion in a command.
     *
     * @return string
     */
    public function encode(): string;
}
