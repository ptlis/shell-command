<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Promise;

use React\Promise\Deferred;

/**
 * Factory that builds and returns React Deferred instances.
 */
final class ReactDeferredFactory implements DeferredFactory
{
    /**
     * Build & return a React Deferred instance.
     *
     * @return Deferred
     */
    public function buildDeferred()
    {
        return new Deferred();
    }
}