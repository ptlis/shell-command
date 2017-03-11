<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration\Environment;

use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

class UnixEnvironmentTest extends ptlisShellCommandTestcase
{
    public function testFullyQualified()
    {
        $this->skipIfNotUnix();

        $command = __DIR__ . '/../../commands/unix/test_binary';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }

    public function testRelative()
    {
        $this->skipIfNotUnix();

        $command = './tests/commands/unix/test_binary';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }


    public function testGlobal()
    {
        $this->skipIfNotUnix();

        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/commands/unix');

        putenv('PATH=/foo/bar:/baz/bat:' . $pathToCommand);

        $env = new UnixEnvironment();

        $valid = $env->validateCommand('test_binary');

        $this->assertSame(true, $valid);

        putenv('PATH=' . $originalPath);
    }

    public function testFromHome()
    {
        $this->skipIfNotUnix();

        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/commands/unix');

        putenv('HOME=' . $pathToCommand);

        $env = new UnixEnvironment();

        $valid = $env->validateCommand('~/test_binary');

        $this->assertSame(true, $valid);

        putenv('PATH=' . $originalPath);
    }

    public function testNotFound()
    {
        $this->skipIfNotUnix();

        $command = 'bob_1_2_3';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(false, $valid);
    }

    public function testCwdOverride()
    {
        $this->skipIfNotUnix();

        $command = './commands/unix/test_binary';
        $cwd = getcwd() . DIRECTORY_SEPARATOR . 'tests';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command, $cwd);

        $this->assertSame(true, $valid);
    }

    public function testPathOverride()
    {
        $this->skipIfNotUnix();

        $paths = [
            realpath(getcwd() . '/tests/commands/unix')
        ];

        $env = new UnixEnvironment($paths);

        $valid = $env->validateCommand('test_binary');

        $this->assertSame(true, $valid);
    }
}
