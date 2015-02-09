<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\ShellCommand;

use ptlis\ShellCommand\ShellSynchronousCommand;
use ptlis\ShellCommand\ShellResult;
use ptlis\ShellCommand\UnixProcess;

class ShellCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testWithFlag()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            $path,
            array(
                '-s bar'
            )
        );

        $this->assertSame(
            $path . ' \'-s bar\'',
            $command->__toString()
        );
    }

    public function testWithArgument()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            $path,
            array(
                '--filter=hide-empty'
            )
        );

        $this->assertSame(
            $path . ' \'--filter=hide-empty\'',
            $command->__toString()
        );
    }

    public function testWithParameter()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            $path,
            array(
                'my_files/'
            )
        );

        $this->assertSame(
            $path . ' \'my_files/\'',
            $command->__toString()
        );
    }

    public function testWithAdHoc()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            )
        );

        $this->assertSame(
            $path . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testRun()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            $path,
            array(
                'if=/dev/sha1 of=/dev/sdb2'
            )
        );

        $this->assertEquals(
            new ShellResult(
                0,
                implode(
                    PHP_EOL,
                    array(
                        'Test command',
                        'if=/dev/sha1 of=/dev/sdb2'
                    )
                ),
                ''
            ),
            $command->run()
        );
    }

    public function testRunWithSleep()
    {
        $path = './tests/data/sleep_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            $path,
            array()
        );

        $this->assertEquals(
            new ShellResult(
                0,
                '',
                ''
            ),
            $command->run()
        );
    }

    public function testRunWithError()
    {
        $path = './tests/data/error_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            $path,
            array()
        );

        $this->assertEquals(
            new ShellResult(
                5,
                '',
                'Fatal Error'
            ),
            $command->run()
        );
    }
}
