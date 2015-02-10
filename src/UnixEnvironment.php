<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\EnvironmentInterface;

/**
 * Implementation of a UNIX environment.
 */
class UnixEnvironment implements EnvironmentInterface
{
    /**
     * Use this working directory in place of the default one.
     *
     * @var string
     */
    private $cwd;

    /**
     * Use the paths stored here in place of the system paths.
     *
     * @var string[]
     */
    private $paths;


    /**
     * Constructor.
     *
     * @param string $cwdOverride
     * @param string[] $pathsOverride
     */
    public function __construct($cwdOverride = '', array $pathsOverride = array())
    {
        $this->setCwd($cwdOverride);
        $this->setPaths($pathsOverride);
    }

    /**
     * {@inheritDoc}
     */
    public function validateCommand($command)
    {
        $valid = false;

        // Fully-qualified path
        if ($this->isValidFullPath($command)) {
            $valid = true;

        // From current directory
        } elseif (strlen($this->isValidRelativePath($command))) {
            $valid = true;

        // In path
        } elseif (strlen($this->isValidGlobalCommand($command))) {
            $valid = true;
        }

        return $valid;
    }

    /**
     * Set the CWD, if $cwdOverride is not set default then default to real CWD.
     *
     * @param string $cwdOverride
     */
    private function setCwd($cwdOverride)
    {
        if (strlen($cwdOverride)) {
            $this->cwd = $cwdOverride;
        } else {
            $this->cwd = getcwd();
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
     *
     * @return bool
     */
    private function isValidRelativePath($relativePath)
    {
        $valid = false;
        if ('./' === substr($relativePath, 0, 2)) {
            $tmpPath = $this->cwd . DIRECTORY_SEPARATOR . substr($relativePath, 2, strlen($relativePath));

            if ($this->isValidFullPath($tmpPath)) {
                $valid = true;
            }
        }

        return $valid;
    }

    /**
     * Validate a global command by checking through system & provided paths.
     *
     * @param string $binaryName
     *
     * @return bool
     */
    private function isValidGlobalCommand($binaryName)
    {
        $valid = false;

        if (strlen($binaryName)) {

            // Check for command in path list
            foreach ($this->paths as $pathDir) {
                $tmpPath = $pathDir . DIRECTORY_SEPARATOR . $binaryName;
                if (file_exists($tmpPath)) {
                    $valid = true;
                    break;
                }
            }
        }

        return $valid;
    }
}
