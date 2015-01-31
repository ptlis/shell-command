<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\ShellCommand;

use ptlis\ShellCommand\Argument\AdHoc;
use ptlis\ShellCommand\Argument\Argument;
use ptlis\ShellCommand\Argument\Flag;
use ptlis\ShellCommand\Interfaces\ArgumentInterface;
use ptlis\ShellCommand\Argument\Parameter;
use ptlis\ShellCommand\ShellSynchronousCommand;
use ptlis\ShellCommand\ShellResult;
use ptlis\ShellCommand\UnixBinary;
use ptlis\ShellCommand\UnixProcess;

class ShellCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testWithFlag()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            new UnixBinary($path),
            array(
                new Flag('s', 'bar')
            )
        );

        $this->assertSame(
            realpath($path) . ' \'-s bar\'',
            $command->__toString()
        );
    }

    public function testWithArgument()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            new UnixBinary($path),
            array(
                new Argument('filter', 'hide-empty', ArgumentInterface::SEPARATOR_EQUALS)
            )
        );

        $this->assertSame(
            realpath($path) . ' \'--filter=hide-empty\'',
            $command->__toString()
        );
    }

    public function testWithParameter()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            new UnixBinary($path),
            array(
                new Parameter('my_files/')
            )
        );

        $this->assertSame(
            realpath($path) . ' \'my_files/\'',
            $command->__toString()
        );
    }

    public function testWithAdHoc()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            new UnixBinary($path),
            array(
                new AdHoc('if=/dev/sha1 of=/dev/sdb2')
            )
        );

        $this->assertSame(
            realpath($path) . ' \'if=/dev/sha1 of=/dev/sdb2\'',
            $command->__toString()
        );
    }

    public function testRun()
    {
        $path = './tests/data/test_binary';

        $command = new ShellSynchronousCommand(
            new UnixProcess(),
            new UnixBinary($path),
            array(
                new AdHoc('if=/dev/sha1 of=/dev/sdb2')
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
            new UnixBinary($path),
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
            new UnixBinary($path),
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