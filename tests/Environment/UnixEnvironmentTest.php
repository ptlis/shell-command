<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Environment;

use ptlis\ShellCommand\UnixEnvironment;

class UnixEnvironmentTest extends \PHPUnit_Framework_TestCase
{
    public function testFullyQualified()
    {
        $command = __DIR__ . '/../data/test_binary';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }

    public function testRelative()
    {
        $command = './tests/data/test_binary';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }


    public function testGlobal()
    {
        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/data');

        putenv('PATH=/foo/bar:/baz/bat:' . $pathToCommand);

        $env = new UnixEnvironment();

        $valid = $env->validateCommand('test_binary');

        $this->assertSame(true, $valid);

        putenv('PATH=' . $originalPath);
    }

    public function testFromHome()
    {
        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/data');

        putenv('HOME=' . $pathToCommand);

        $env = new UnixEnvironment();

        $valid = $env->validateCommand('~/test_binary');

        $this->assertSame(true, $valid);

        putenv('PATH=' . $originalPath);
    }

    public function testNotFound()
    {
        $command = 'bob_1_2_3';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(false, $valid);
    }

    public function testCwdOverride()
    {
        $command = './data/test_binary';
        $cwd = getcwd() . DIRECTORY_SEPARATOR . 'tests';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command, $cwd);

        $this->assertSame(true, $valid);
    }

    public function testPathOverride()
    {
        $paths = array(
            realpath(getcwd() . '/tests/data')
        );

        $env = new UnixEnvironment($paths);

        $valid = $env->validateCommand('test_binary');

        $this->assertSame(true, $valid);
    }
}
