<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
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
    public function tearDown(): void
    {
        global $mockProcTerminate;
        global $mockProcTerminateRetval;
        global $mockProcTerminateCalled;

        $mockProcTerminate = false;
        $mockProcTerminateRetval = false;
        $mockProcTerminateCalled = false;
    }

    public function testFullyQualified(): void
    {
        $command = __DIR__ . '/../commands/unix/test_binary';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }

    public function testRelative(): void
    {
        $command = './tests/commands/unix/test_binary';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(true, $valid);
    }

    public function testGlobal(): void
    {
        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/commands/unix');

        putenv('PATH=/foo/bar:/baz/bat:' . $pathToCommand);

        $env = new UnixEnvironment();

        $valid = $env->validateCommand('test_binary');

        $this->assertSame(true, $valid);

        putenv('PATH=' . $originalPath);
    }

    public function testFromHome(): void
    {
        $originalPath = getenv('PATH');

        $pathToCommand = realpath(getcwd() . '/tests/commands/unix');

        putenv('HOME=' . $pathToCommand);

        $env = new UnixEnvironment();

        $valid = $env->validateCommand('~/test_binary');

        $this->assertSame(true, $valid);

        putenv('PATH=' . $originalPath);
    }

    public function testNotFound(): void
    {
        $command = 'bob_1_2_3';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command);

        $this->assertSame(false, $valid);
    }

    public function testCwdOverride(): void
    {
        $command = './commands/unix/test_binary';
        $cwd = getcwd() . DIRECTORY_SEPARATOR . 'tests';

        $env = new UnixEnvironment();

        $valid = $env->validateCommand($command, $cwd);

        $this->assertSame(true, $valid);
    }

    public function testPathOverride(): void
    {
        $paths = [
            realpath(getcwd() . '/tests/commands/unix')
        ];

        $env = new UnixEnvironment($paths);

        $valid = $env->validateCommand('test_binary');

        $this->assertSame(true, $valid);
    }

    public function testExpandPath(): void
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

    public function testSendSigTerm(): void
    {
        global $mockProcTerminate;
        global $mockProcTerminateRetval;
        global $mockProcTerminateCalled;

        $mockProcTerminate = true;
        $mockProcTerminateRetval = true;
        $mockProcTerminateCalled = false;

        $environment = new UnixEnvironment();
        $environment->sendSignal(null, ProcessInterface::SIGTERM);

        $this->assertTrue($mockProcTerminateCalled);
    }

    public function testSendSigKill(): void
    {
        global $mockProcTerminate;
        global $mockProcTerminateRetval;
        global $mockProcTerminateCalled;
        $mockProcTerminateCalled = false;

        $mockProcTerminate = true;
        $mockProcTerminateRetval = true;

        $environment = new UnixEnvironment();
        $environment->sendSignal(null, ProcessInterface::SIGKILL);

        $this->assertTrue($mockProcTerminateCalled);
    }

    public function testSendInvalidSignal(): void
    {
        $this->expectException(\RuntimeException::class);

        global $mockProcTerminate;
        global $mockProcTerminateRetval;

        $mockProcTerminate = true;
        $mockProcTerminateRetval = true;

        $environment = new UnixEnvironment();
        $environment->sendSignal(null, 'FOOBAR');
    }

    public function testSignalError(): void
    {
        $this->expectException(\RuntimeException::class);

        global $mockProcTerminate;
        global $mockProcTerminateRetval;

        $mockProcTerminate = true;
        $mockProcTerminateRetval = false;

        $environment = new UnixEnvironment();
        $environment->sendSignal(null, ProcessInterface::SIGTERM);
    }
}
