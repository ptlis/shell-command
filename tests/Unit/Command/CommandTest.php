<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\ShellCommand;

use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Mock\MockEnvironment;
use ptlis\ShellCommand\Command;
use ptlis\ShellCommand\SudoUser;

class CommandTest extends \PHPUnit_Framework_TestCase
{
    public function testWithFlag()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            array(
                '-s bar'
            ),
            getcwd()
        );

        $this->assertSame(
            $path . ' \'-s bar\'',
            $command->__toString()
        );
    }

    public function testWithArgument()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            array(
                '--filter=hide-empty'
            ),
            getcwd()
        );

        $this->assertSame(
            $path . ' \'--filter=hide-empty\'',
            $command->__toString()
        );
    }

    public function testWithParameter()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            array(
                'my_files/'
            ),
            getcwd()
        );

        $this->assertSame(
            $path . ' \'my_files/\'',
            $command->__toString()
        );
    }

    public function testWithAdHoc()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            ),
            getcwd()
        );

        $this->assertSame(
            $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testWithSudoPassword()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            ),
            getcwd(),
            -1,
            1000,
            new SudoUser('', 'testpass')
        );

        $this->assertSame(
            'run_as_root -u \'\' -p \'testpass\' ' . $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testWithSudoPasswordAndUsername()
    {
        $path = './tests/commands/unix/test_binary';

        $command = new Command(
            new MockEnvironment(),
            new NullProcessObserver(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            ),
            getcwd(),
            -1,
            1000,
            new SudoUser('testuser', 'testpass')
        );

        $this->assertSame(
            'run_as_root -u \'testuser\' -p \'testpass\' ' . $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }
}
