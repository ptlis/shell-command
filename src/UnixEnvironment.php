<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Exceptions\CommandExecutionException;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessInterface;

/**
 * Implementation of a UNIX environment.
 */
final class UnixEnvironment implements EnvironmentInterface
{
    /** @var array<string> */
    private array $paths;

    public function __construct(array $pathsOverride = [])
    {
        $this->paths = \count($pathsOverride)
            ? $pathsOverride
            : \explode(':', getenv('PATH'));
    }

    public function validateCommand(string $command, string $cwdOverride = ''): bool
    {
        $cwd = $this->normalizeCwd($cwdOverride);

        return (
            $this->isValidFullPath($command)
            || $this->isValidHomeDirectory($command)
            || $this->isValidRelativePath($command, $cwd)
            || $this->isValidGlobalCommand($command)
        );
    }

    public function sendSignal($process, string $signal): void
    {
        switch ($signal) {
            case ProcessInterface::SIGTERM:
                $this->safeSendSignal($process, $signal, SIGTERM);
                break;

            case ProcessInterface::SIGKILL:
                $this->safeSendSignal($process, $signal, SIGKILL);
                break;

            default:
                throw new CommandExecutionException(
                    'Unknown signal "' . $signal . '" provided.'
                );
        }
    }

    public function expandPath(string $path): string
    {
        if ($this->isValidHomeDirectory($path)) {
            $path = $this->expandHomeDirectory($path);
        }

        return $path;
    }

    public function getSupportedList(): array
    {
        return [
            'Linux',
            'Darwin'
        ];
    }

    public function escapeShellArg(string $arg): string
    {
        return \escapeshellarg($arg);
    }

    /**
     * 'Safe' send signal method; throws an exception if the signal send fails for any reason.
     *
     * @throws CommandExecutionException on error.
     */
    private function safeSendSignal($process, string $signal, int $mappedSignal): void
    {
        if (true !== proc_terminate($process, $mappedSignal)) {
            throw new CommandExecutionException(
                'Call to proc_terminate with signal "' . $signal . '" failed for unknown reason.'
            );
        }
    }

    /**
     * Normalize CWD - if Override is set return that otherwise return the real CWD.
     */
    private function normalizeCwd(string $cwdOverride): string
    {
        if (strlen($cwdOverride)) {
            return $cwdOverride;
        } else {
            return getcwd();
        }
    }

    /**
     * Expands home director '~/' to the full path to the home directory of the user who is executing the command.
     */
    private function expandHomeDirectory(string $path): string
    {
        return getenv('HOME') . DIRECTORY_SEPARATOR . substr($path, 2, strlen($path));
    }

    /**
     * Returns true if the path is relative to the users home directory.
     *
     * The home directory receives special attention due to the fact that chdir (and pals) don't expand '~' to the users
     *  home directory - this is a function of the shell on UNIX systems so we must replicate the behaviour here.
     */
    private function isValidHomeDirectory(string $path): bool
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
     */
    private function isValidFullPath(string $path): bool
    {
        $valid = false;
        if ('/' === substr($path, 0, 1) && is_executable($path)) {
            $valid = true;
        }

        return $valid;
    }

    /**
     * Validate a relative command path.
     */
    private function isValidRelativePath(string $relativePath, string $cwd): bool
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
     */
    private function isValidGlobalCommand(string $command): bool
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
}
