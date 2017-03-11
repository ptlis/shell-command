<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Exceptions\CommandExecutionException;
use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;
use ptlis\ShellCommand\Interfaces\ProcessInterface;

/**
 * Implementation of a UNIX environment.
 */
final class UnixEnvironment implements EnvironmentInterface
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
    public function __construct(array $pathsOverride = [])
    {
        $this->setPaths($pathsOverride);
    }

    /**
     * @inheritDoc
     */
    public function validateCommand($command, $cwdOverride = '')
    {
        $valid = false;

        $cwd = $this->normalizeCwd($cwdOverride);

        // Fully-qualified path
        if ($this->isValidFullPath($command)) {
            $valid = true;

        // In users home directory
        } elseif ($this->isValidHomeDirectory($command)) {
            $valid = true;

        // From current directory
        } elseif ($this->isValidRelativePath($command, $cwd)) {
            $valid = true;

        // In path
        } elseif ($this->isValidGlobalCommand($command)) {
            $valid = true;
        }

        return $valid;
    }

    /**
     * @inheritDoc
     *
     * @throws CommandExecutionException on error.
     */
    public function sendSignal($process, $signal)
    {
        switch ($signal) {
            case ProcessInterface::SIGTERM:
                $mappedSignal = SIGTERM;
                break;

            case ProcessInterface::SIGKILL:
                $mappedSignal = SIGKILL;
                break;

            default:
                throw new CommandExecutionException(
                    'Unknown signal "' . $signal . '" provided.'
                );
        }

        if (true !== proc_terminate($process, $mappedSignal)) {
            throw new CommandExecutionException(
                'Call to proc_terminate with signal "' . $signal . '" failed for unknown reason.'
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function expandPath($path)
    {
        if ($this->isValidHomeDirectory($path)) {
            $path = $this->expandHomeDirectory($path);
        }

        return $path;
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
     * @param string $path
     *
     * @return string
     */
    private function expandHomeDirectory($path)
    {
        return getenv('HOME') . DIRECTORY_SEPARATOR . substr($path, 2, strlen($path));
    }

    /**
     * Returns true if the path is relative to the users home directory.
     *
     * The home directory receives special attention due to the fact that chdir (and pals) don't expand '~' to the users
     *  home directory - this is a function of the shell on UNIX systems so we must replicate the behaviour here.
     *
     * @param string $path
     *
     * @return bool
     */
    private function isValidHomeDirectory($path)
    {
        $valid = false;
        if ('~/' === substr($path, 0, 2)) {
            $valid = $this->isValidFullPath(
                $this->expandHomeDirectory($path)
            );
        }

        return $valid;
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

            $valid = $this->isValidFullPath($tmpPath);
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
                if ($this->isValidFullPath($tmpPath)) {
                    $valid = true;
                    break;
                }
            }
        }

        return $valid;
    }

    /**
     * @inheritDoc
     */
    public function getSupportedList()
    {
        return [
            'Linux',
            'Darwin'
        ];
    }

    /**
     * @inheritDoc
     */
    public function escapeShellArg($arg)
    {
        return escapeshellarg($arg);
    }

    /**
     * @inheritDoc
     */
    public function applyEnvironmentVariables($command, array $envVariableList)
    {
        $envVariablePrefix = '';
        foreach ($envVariableList as $key => $value) {
            $envVariablePrefix .= $key . '=' . $this->escapeShellArg($value) . ' ';
        }

        return $envVariablePrefix . ' ' . $command;
    }
}
