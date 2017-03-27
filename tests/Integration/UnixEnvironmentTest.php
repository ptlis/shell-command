<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration;

use ptlis\ShellCommand\Interfaces\ProcessInterface;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\UnixEnvironment
 */
class UnixEnvironmentTest extends ptlisShellCommandTestcase
{
    public function tearDown()
    {
        global $mockProcTerminate;
        global $mockProcTerminateRetval;

        $mockProcTerminate = false;
        $mockProcTerminateRetval = false;
    }

    public function testFullyQualified()
    {
        $command = __DIR__ . '/../commands/unix/test_binary';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }

    public function testRelative()
    {
        $command = './tests/commands/unix/test_binary';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }

    public function testGlobal()
    {
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
        $command = 'bob_1_2_3';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(false, $valid);
    }

    public function testCwdOverride()
    {
        $command = './commands/unix/test_binary';
        $cwd = getcwd() . DIRECTORY_SEPARATOR . 'tests';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command, $cwd);

        $this->assertSame(true, $valid);
    }

    public function testPathOverride()
    {
        $paths = [
            realpath(getcwd() . '/tests/commands/unix')
        ];

        $env = new UnixEnvironment($paths);

        $valid = $env->validateCommand('test_binary');

        $this->assertSame(true, $valid);
    }

    public function testExpandPath()
    {
        global $mockIsExecutable;
        $mockIsExecutable = true;

        $path = '~/foo/bar';

        $homeDir = getenv('HOME');

        $environment = new UnixEnvironment();

        $this->assertSame(
            $homeDir . '/foo/bar',
            $environment->expandPath($path)
        );

        $mockIsExecutable = false;
    }

    public function testSendSigTerm()
    {
        global $mockProcTerminate;
        global $mockProcTerminateRetval;

        $mockProcTerminate = true;
        $mockProcTerminateRetval = true;

        $environment = new UnixEnvironment();
        $environment->sendSignal(null, ProcessInterface::SIGTERM);
    }

    public function testSendSigKill()
    {
        global $mockProcTerminate;
        global $mockProcTerminateRetval;

        $mockProcTerminate = true;
        $mockProcTerminateRetval = true;

        $environment = new UnixEnvironment();
        $environment->sendSignal(null, ProcessInterface::SIGKILL);
    }

    public function testSendInvalidSignal()
    {
        $this->setExpectedException('\RuntimeException');

        global $mockProcTerminate;
        global $mockProcTerminateRetval;

        $mockProcTerminate = true;
        $mockProcTerminateRetval = true;

        $environment = new UnixEnvironment();
        $environment->sendSignal(null, 'FOOBAR');
    }

    public function testSignalError()
    {
        $this->setExpectedException('\RuntimeException');

        global $mockProcTerminate;
        global $mockProcTerminateRetval;

        $mockProcTerminate = true;
        $mockProcTerminateRetval = false;

        $environment = new UnixEnvironment();
        $environment->sendSignal(null, ProcessInterface::SIGTERM);
    }
}
