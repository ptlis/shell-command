<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Exceptions\InvalidBinaryException;
use ptlis\ShellCommand\Interfaces\BinaryInterface;

/**
 * Implementation of the binary interface - wraps around a binary file.
 */
class UnixBinary implements BinaryInterface
{
    /** @var string Fully qualified path to binary. */
    private $path;


    /**
     * Constructor.
     *
     * @param string $binary
     */
    public function __construct($binary)
    {
        // Fully-qualified path
        if ($this->isValidFullPath($binary)) {
            $this->path = realpath($binary);

        // From current directory
        } elseif (strlen($path = $this->getFullPathFromRelative($binary))) {
            $this->path = realpath($path);

        // In path
        } elseif (strlen($path = $this->getFullPathFromGlobal($binary))) {
            $this->path = realpath($path);

        // Not found
        } else {
            throw new InvalidBinaryException($binary);
        }
    }

    /**
     * Return a string representation of the binary.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->path;
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
     * Convert a relative path to a fully qualified one.
     *
     * @param $relativePath
     *
     * @return string
     */
    private function getFullPathFromRelative($relativePath)
    {
        $path = '';
        if ('./' === substr($relativePath, 0, 2)) {
            $tmpPath = getcwd() . DIRECTORY_SEPARATOR . substr($relativePath, 2, strlen($relativePath));

            if ($this->isValidFullPath($tmpPath)) {
                $path = $tmpPath;
            }
        }

        return $path;
    }

    /**
     * Convert a global binary name to a fully qualified path.
     *
     * @param string $binaryName
     *
     * @return string
     */
    private function getFullPathFromGlobal($binaryName)
    {
        $path = '';

        // Check for binary in $PATH
        $pathDirList = explode(':', getenv('PATH'));
        foreach ($pathDirList as $pathDir) {
            $tmpPath = $pathDir . DIRECTORY_SEPARATOR . $binaryName;
            if (file_exists($tmpPath)) {
                $path = $tmpPath;
                break;
            }
        }

        return $path;
    }
}
