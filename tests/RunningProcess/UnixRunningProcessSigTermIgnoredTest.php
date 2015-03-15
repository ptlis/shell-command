<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\RunningProcess;

class UnixRunningProcessSigTermIgnoredTest extends \PHPUnit_Framework_TestCase
{
    public function testRunProcess()
    {
        $command = './tests/data/sleep_binary';

        $process = new UnixRunningProcessBlockSigTerm($command, getcwd());

        $process->stop();

        $this->assertEquals(
            false,
            $process->isRunning()
        );

        $this->assertEquals(
            './tests/data/sleep_binary',
            $process->getCommand()
        );
    }
}
