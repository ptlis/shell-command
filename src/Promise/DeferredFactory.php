<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Promise;

use React\Promise\Deferred;

/**
 * Factory that builds and returns deferred instances.
 *
 * This factory allows library consumers to use a custom Deferred instance derived from React's.
 */
interface DeferredFactory
{
    /**
     * Build & return a deferred instance.
     *
     * @return Deferred
     */
    public function buildDeferred();
}