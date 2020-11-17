<?php declare(strict_types=1);

/**
 * @copyright (c) 2015-present brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit;

use ptlis\ShellCommand\Logger\AllLogger;
use ptlis\ShellCommand\Logger\NullProcessObserver;
use ptlis\ShellCommand\Test\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\CommandBuilder;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\CommandBuilder
 */
class CommandBuilderTest extends ptlisShellCommandTestcase
{
    private function readAttribute($object, string $property)
    {
        $reflectionObject = new \ReflectionClass(get_class($object));
        $property = $reflectionObject->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    public function testDetectEnvironmentSuccess(): void
    {
        $builder = new CommandBuilder();

        $environment = $builder->getEnvironment('Linux');

        $this->assertEquals(
            new UnixEnvironment(),
            $environment
        );
    }

    public function testDetectEnvironmentError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to find Environment for OS "foobar".');

        $builder = new CommandBuilder();

        $builder->getEnvironment('foobar');
    }

    public function testBasic(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testArgument(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('--foo bar')
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\'',
            $command->__toString()
        );
    }

    public function testConditionalArgumentFalse(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('--foo bar', false)
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testConditionalArgumentTrue(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArgument('--foo bar', true)
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\'',
            $command->__toString()
        );
    }

    public function testArgumentList(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments(
                [
                    '--foo bar',
                    'baz'
                ]
            )
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\' \'baz\'',
            $command->__toString()
        );
    }

    public function testConditionalArgumentListFalse(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments(
                [
                    '--foo bar',
                    'baz'
                ],
                false
            )
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testConditionalArgumentListTrue(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments(
                [
                    '--foo bar',
                    'baz'
                ],
                true
            )
            ->buildCommand();

        $this->assertEquals(
            $path . ' \'--foo bar\' \'baz\'',
            $command->__toString()
        );
    }

    public function testRawArgument(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArgument('--foo \'bar\'')
            ->buildCommand();

        $this->assertEquals(
            $path . ' --foo \'bar\'',
            $command->__toString()
        );
    }

    public function testConditionalRawArgumentFalse(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArgument('--foo \'bar\'', false)
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testConditionalRawArgumentTrue(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArgument('--foo \'bar\'', true)
            ->buildCommand();

        $this->assertEquals(
            $path . ' --foo \'bar\'',
            $command->__toString()
        );
    }

    public function testRawArgumentList(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArguments(
                [
                    '--foo bar',
                    'baz'
                ]
            )
            ->buildCommand();

        $this->assertEquals(
            $path . ' --foo bar baz',
            $command->__toString()
        );
    }

    public function testRawArgumentFalse(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArguments(
                [
                    '--foo bar',
                    'baz'
                ],
                false
            )
            ->buildCommand();

        $this->assertEquals(
            $path,
            $command->__toString()
        );
    }

    public function testRawArgumentTrue(): void
    {
        $path = './tests/commands/unix/test_binary';
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addRawArguments(
                [
                    '--foo bar',
                    'baz'
                ],
                true
            )
            ->buildCommand();

        $this->assertEquals(
            $path . ' --foo bar baz',
            $command->__toString()
        );
    }

    public function testPollTimeout(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setPollTimeout(1234567)
            ->buildCommand();

        $this->assertSame(
            1234567,
            $this->readAttribute($command, 'pollTimeout')
        );
    }

    public function testTimeout(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            60 * 1000 * 1000,
            $this->readAttribute($command, 'timeout')
        );
    }

    public function testEnvironmentVariable(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->addEnvironmentVariable('TEST', 'VALUE')
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            ['TEST' => 'VALUE'],
            $this->readAttribute($command, 'envVariableList')
        );
    }

    public function testEnvironmentVariableFalse(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->addEnvironmentVariable('TEST', 'VALUE', false)
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            [],
            $this->readAttribute($command, 'envVariableList')
        );
    }

    public function testEnvironmentVariableTrue(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->addEnvironmentVariable('TEST', 'VALUE', true)
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            ['TEST' => 'VALUE'],
            $this->readAttribute($command, 'envVariableList')
        );
    }

    public function testEnvironmentVariable2(): void
    {
        $path = './tests/commands/unix/test_binary';
        $arguments = [
            '--foo bar',
            'baz'
        ];
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->setCommand($path)
            ->addArguments($arguments)
            ->addEnvironmentVariables([
                'ABC' => '123',
                'TEST' => 'VALUE'
            ])
            ->setPollTimeout(1000 * 1000)
            ->setTimeout(60 * 1000 * 1000)
            ->buildCommand();

        $this->assertSame(
            [
                'ABC' => '123',
                'TEST' => 'VALUE'
            ],
            $this->readAttribute($command, 'envVariableList')
        );
    }

    public function testAddSingleLogger(): void
    {
        $builder = new CommandBuilder(new UnixEnvironment());

        $command = $builder
            ->addArgument('test')
            ->setCommand('./tests/commands/unix/test_binary')
            ->addProcessObserver(
                new NullProcessObserver()
            )
            ->buildCommand();

        $this->assertInstanceOf(
            'ptlis\ShellCommand\Logger\NullProcessObserver',
            $this->readAttribute($command, 'processObserver')
        );
    }

    public function testAddMultipleLogger(): void
    {
        $builder = new CommandBuilder(new UnixEnvironment());

        $logger = new MockPsrLogger();

        $command = $builder
            ->addArgument('test')
            ->setCommand('./tests/commands/unix/test_binary')
            ->addProcessObserver(
                new AllLogger($logger)
            )
            ->addProcessObserver(
                new NullProcessObserver()
            )
            ->buildCommand();

        $this->assertInstanceOf(
            'ptlis\ShellCommand\Logger\AggregateLogger',
            $this->readAttribute($command, 'processObserver')
        );
    }
}
