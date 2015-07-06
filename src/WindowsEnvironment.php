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
        // TODO: Implement validateCommand() method.
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
}
