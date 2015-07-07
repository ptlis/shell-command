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
use ptlis\ShellCommand\Interfaces\RunningProcessInterface;

/**
 * Implementation of a Windows environment.
 */
class WindowsEnvironment implements EnvironmentInterface
{
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
}
