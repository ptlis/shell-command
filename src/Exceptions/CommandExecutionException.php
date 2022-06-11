<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Exceptions;

use RuntimeException;

/**
 * Exception thrown when an error occurs executing a command.
 */
final class CommandExecutionException extends RuntimeException
{
}
