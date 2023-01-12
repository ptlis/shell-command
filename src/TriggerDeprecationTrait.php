<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand;

trait TriggerDeprecationTrait
{
    protected function triggerDeprecationWarning(string $method, string $propertyName): void
    {
        \trigger_error(
            'Replace calls to `' . $method . '()` with direct property access like `'
            . __CLASS__ . '::' . $propertyName . '`',
            E_USER_DEPRECATED
        );
    }
}
