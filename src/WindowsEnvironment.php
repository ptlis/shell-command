<?php

/**
 * PHP Version 5.3
 *
 * @copyright   (c) 2015 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Interfaces\CommandInterface;
use ptlis\ShellCommand\Interfaces\EnvironmentInterface;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;

/**
 * Implementation of a Windows environment.
 */
class WindowsEnvironment implements EnvironmentInterface
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
     * @inheritDoc
     */
    public function validateCommand($command, $cwdOverride = '')
    {
        $valid = false;

        $cwd = $this->normalizeCwd($cwdOverride);

        // Fully-qualified path
        if ($this->isValidFullPath($command)) {
            $valid = true;

        // From current directory
        } elseif ($this->isValidRelativePath($command, $cwd)) {
            $valid = true;

        // In path
        } elseif ($this->isValidGlobalCommand($command)) {
            $valid = true;
        }

        // TODO:
        //  In users home directory
        //  In path

        return $valid;
    }

    /**
     * @inheritDoc
     */
    public function buildProcess(
        CommandInterface $command,
        $cwd,
        ProcessObserverInterface $processObserver,
        $timeout = -1,
        $pollTimeout = 1000
    ) {
        // TODO: Implement buildProcess() method.
    }

    /**
     * @inheritDoc
     */
    public function sendSignal($process, $signal)
    {
        // TODO: Implement sendSignal() method.
    }

    /**
     * @inheritDoc
     */
    public function getSupportedList()
    {
        return array(
            'Windows',
            'WINNT',
            'WIN32'
        );
    }

    /**
     * @inheritDoc
     */
    public function escapeShellArg($arg)
    {
        return escapeshellarg($arg);
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
            $this->paths = explode(';', getenv('PATH'));
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

        // TODO: Handle network shares?
        if (1 === preg_match('/^[a-z]+:\\\\/i', $path) && is_file($path)) {
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
        return $this->isValidFullPath($cwd . DIRECTORY_SEPARATOR . $relativePath);
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
}
