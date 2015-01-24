<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Interfaces;

/**
 * Builder to create a command ready to execute.
 */
interface CommandBuilderInterface
{
    /**
     * Set the binary to execute.
     *
     * @param $binary
     *
     * @return $this
     */
    public function setBinary($binary);

    /**
     * Add an argument to the command.
     *
     * @param string $argument
     * @param string $value
     * @param string $separator
     *
     * @return $this
     */
    public function addArgument($argument, $value = '', $separator = ArgumentInterface::SEPARATOR_SPACE);

    /**
     * Add a flag to the command.
     *
     * @param string $flag
     * @param string $value
     *
     * @return $this
     */
    public function addFlag($flag, $value = '');

    /**
     * Add a parameter to the command.
     *
     * @param string $parameter
     *
     * @return $this
     */
    public function addParameter($parameter);

    /**
     * Add an ad-hoc argument, useful for non-standard and old commands.
     *
     * @param string $argument
     *
     * @return $this
     */
    public function addAdHoc($argument);

    /**
     * Gets the built command & resets the builder.
     *
     * @return SynchronousCommandInterface
     */
    public function getCommand();

    /**
     * Clear & reset the builder to default state.
     */
    public function clear();
}
