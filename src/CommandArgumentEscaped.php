<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandArgumentInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;

/**
 * Value object representing a command argument requiring escaping.
 */
final class CommandArgumentEscaped implements CommandArgumentInterface
{
    /**
     * @var string
     */
    private $argument;

    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @param string $argument
     */
    public function __construct(
        $argument,
        EnvironmentInterface $environment
    ) {
        $this->argument = $argument;
        $this->environment = $environment;
    }

    /**
     * @inheritDoc
     */
    public function encode()
    {
        return $this->environment->escapeShellArg($this->argument);
    }
}