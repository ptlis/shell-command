<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\RunningProcessInterface;

/**
 * Implementation of a UNIX environment.
 */
class UnixEnvironment implements EnvironmentInterface
{
    /**
     * Use the paths stored here in place of the system paths.
     *
     * @var string[]
     */
    private $paths;


    /**
     * Constructor.
     *
     * @param string[] $pathsOverride
     */
    public function __construct(array $pathsOverride = array())
    {
        $this->setPaths($pathsOverride);
    }

    /**
     * {@inheritDoc}
     */
    public function validateCommand($command, $cwdOverride = '')
    {
        $valid = false;

        $cwd = $this->normalizeCwd($cwdOverride);

        // Fully-qualified path
        if ($this->isValidFullPath($command)) {
            $valid = true;

        // From current directory
        } elseif (strlen($this->isValidRelativePath($command, $cwd))) {
            $valid = true;

        // In path
        } elseif (strlen($this->isValidGlobalCommand($command))) {
            $valid = true;
        }

        return $valid;
    }

    /**
     * {@inheritDoc}
     */
    public function buildProcess(CommandInterface $command, $cwd, $timeout = -1, $pollTimeout = 1000)
    {
        return new UnixRunningProcess(
            $command,
            $cwd,
            $timeout,
            $pollTimeout
        );
    }

    /**
     * Normalize CWD - if Override is set return that otherwise return the real CWD.
     *
     * @param string $cwdOverride
     *
     * @return string Normalized CWD.
     */
    private function normalizeCwd($cwdOverride)
    {
        if (strlen($cwdOverride)) {
            return $cwdOverride;
        } else {
            return getcwd();
        }
    }

    /**
     * Set the paths to look for global commands, if $pathsOverride not set then default to system paths.
     *
     * @param string[] $pathsOverride
     */
    private function setPaths(array $pathsOverride)
    {
        if (count($pathsOverride)) {
            $this->paths = $pathsOverride;
        } else {
            $this->paths = explode(':', getenv('PATH'));
        }
    }

    /**
     * Returns true if the path points to an executable file.
     *
     * @param string $path
     *
     * @return bool
     */
    private function isValidFullPath($path)
    {
        $valid = false;
        if ('/' === substr($path, 0, 1) && is_executable($path)) {
            $valid = true;
        }

        return $valid;
    }

    /**
     * Validate a relative command path.
     *
     * @param string $relativePath
     * @param string $cwd
     *
     * @return bool
     */
    private function isValidRelativePath($relativePath, $cwd)
    {
        $valid = false;
        if ('./' === substr($relativePath, 0, 2)) {
            $tmpPath = $cwd . DIRECTORY_SEPARATOR . substr($relativePath, 2, strlen($relativePath));

            if ($this->isValidFullPath($tmpPath)) {
                $valid = true;
            }
        }

        return $valid;
    }

    /**
     * Validate a global command by checking through system & provided paths.
     *
     * @param string $command
     *
     * @return bool
     */
    private function isValidGlobalCommand($command)
    {
        $valid = false;

        if (strlen($command)) {

            // Check for command in path list
            foreach ($this->paths as $pathDir) {
                $tmpPath = $pathDir . DIRECTORY_SEPARATOR . $command;
                if (file_exists($tmpPath)) {
                    $valid = true;
                    break;
                }
            }
        }

        return $valid;
    }
}
