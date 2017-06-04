# ptlis/shell-command

A developer-friendly wrapper around execution of shell commands.

There were several goals that inspired the creation of this package:

* Use the [command pattern](https://en.wikipedia.org/wiki/Command_pattern) to encapsulate the data required to execute a shell command to allow it to be passed around and executed later.
* Maintain a stateless object graph allowing (for example) the spawning of multiple running processes from a single command.
* Provide clean APIs for synchronous and asynchronous usage.
* Running processes can be wrapped in promises to allow for easy composition.



[![Build Status](https://travis-ci.org/ptlis/shell-command.png?branch=master)](https://travis-ci.org/ptlis/shell-command) [![Code Coverage](https://scrutinizer-ci.com/g/ptlis/shell-command/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ptlis/shell-command/) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ptlis/shell-command/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ptlis/shell-command/) [![Latest Stable Version](https://poser.pugx.org/ptlis/shell-command/v/stable.png)](https://packagist.org/packages/ptlis/shell-command)


## Install

From the terminal:

```shell
    $ composer require ptlis/shell-command
```



## Usage

### The Builder

The package ships with a command builder, providing a simple and safe method to build commands. 

```php
    use ptlis\ShellCommand\CommandBuilder;
    
    $builder = new CommandBuilder();
```

The builder will attempt to determine your environment when constructed, you can override this by specifying an environment as the first argument:

```php

    use ptlis\ShellCommand\CommandBuilder;
    use ptlis\ShellCommand\UnixEnvironment;
    
    $builder = new CommandBuilder(new UnixEnvironment());
```

Note this builder is immutable - method calls must be chained and terminated with a call to ```buildCommand``` like so:
 
```php
    $command = $builder
        ->setCommand('foo')
        ->addArgument('--bar=baz')
        ->buildCommand()
``` 


#### Add Command

First we must provide the command to execute:

```php
    $builder->setCommand('git')             // Command in $PATH
        
    $builder->setCommand('./local/bin/git') // Relative to current working directory
        
    $builder->setCommand('/usr/bin/git')    // Fully qualified path to binary
```

If the command is not locatable a ```RuntimeException``` is thrown.


#### Set Timeout

Setting the timeout (in microseconds) sets how long the library will wait on a process before termination. Defaults to -1 which never forces termination.

```php
    $builder->setTimeout(30 * 1000 * 1000)          // Wait 30 seconds
```

If the process execution time exceeds this value a SIGTERM will be sent; if the process then doesn't terminate after a further 1 second wait then a SIGKILL is sent.



#### Add Arguments

Add arguments to invoke the command with (all arguments are escaped):

```php
    $builder
        ->addArgument('--foo=bar')
```

Or in bulk:

```php
    $builder
        ->addArguments(array(
            '--foo=bar',
            '-xzcf',
            'if=/dev/sda of=/dev/sdb'
        ))
```



#### Add Raw Arguments

**WARNING**: Do not pass user-provided data to these methods! Malicious users could easily execute arbitrary shell commands. 

Arguments can also be applied without escaping:

```php
    $builder
        ->addRawArgument("--foo='bar'")
```

Or in bulk:

```php
    $builder
        ->addRawArguments(array(
            "--foo='bar'",
            '-xzcf',
        ))
```


#### Set Environment Variables

Environment variables can be set when running a command:

```php
    $builder
        ->addEnvironmentVariable(
            'TEST_VARIABLE',
            '123'
        )
```

Or in bulk:

```php
    $builder
        ->addEnvironmentVariables([
            'TEST_VARIABLE' => '123',
            'FOO' => 'bar'
        ])
```



#### Add Process Observers

Observers can be attached to spawned processes. In this case we add a simple logger:

```php
    $builder
        ->addProcessObserver(
            new AllLogger(
                new DiskLogger(),
                LogLevel::DEBUG
            )
        )
```


#### Build the Command

One the builder has been configured, the command can be retrieved for execution:

```php
    $command = $builder
        // Command configuration...
        ->buildCommand();
```



### Synchronous Execution

To run a command synchronously use the ```runSynchronous``` method. This returns an object implementing ```CommandResultInterface```, encoding the result of the command.

```php
    $result = $command->runSynchronous(); 
```

When you need to re-run the same command multiple times you can simply invoke ```runSynchronous``` repeatedly; each call will run the command returning the result to your application.

The exit code & output of the command are available as methods on this object:

```php
    $result->getExitCode();     // 0 for success, anything else conventionally indicates an error
    $result->getStdOut();       // The contents of stdout (as a string)
    $result->getStdOutLines();  // The contents of stdout (as an array of lines)
    $result->getStdErr();       // The contents of stderr (as a string)
    $result->getStdErrLines();  // The contents of stderr (as an array of lines)
```



### Asynchronous execution

Commands can also be executed asynchronously, allowing your program to continue executing while waiting for the result.



#### Command::runPromise

Monitoring of shell command execution can be wrapped in a [ReactPHP Promise](https://github.com/reactphp/promise). This gives us a flexible execution model, allowing chaining (with [Promise::then](https://github.com/reactphp/promise#promiseinterfacethen)) and aggregation using methods like [Promise::all](https://github.com/reactphp/promise#all), [Promise::some](https://github.com/reactphp/promise#some), [Promise::race](https://github.com/reactphp/promise#race) and their friends.
 
Building promise to execute a command can be done with the ```runPromise``` method. This returns an instance of ```\React\Promise\Promise```:

```php
    $eventLoop = \React\EventLoop\Factory::create();

    $promise = $command->runPromise($eventLoop);
```

The [ReactPHP EventLoop](https://github.com/reactphp/event-loop) component is used to periodically poll the running process to see if it has terminated yet; once it has the promise is either resolved or rejected depending on the exit code of the executed command.

The effect of this implementation is that once you've created your promises, chains and aggregates you must invoke ```EventLoop::run```:

```php
    $eventLoop->run();
```

This will block further execution until the promises are resolved/rejected.



#### Command::runAsynchronous

The ```runAsynchronous``` method returns an object implementing the ```ProcessInterface``` which provides methods to monitor the state of a process.

```php
    $process = $command->runAsynchronous();
```

As with the synchronouse API, when you need to re-run the same command multiple times you can simply invoke ```runAsynchronous``` repeatedly; each call will run the command returning the object representing the process to your application.

```ProcessInterface``` provides the methods required to monitor the state and lifecycle of a process.

Check whether the process has completed:

```php
    if (!$process->isisRunning()) {
        echo 'done' . PHP_EOL;
    }
```

Force the process to stop:

```php
    $process->stop();
```

Wait for the process to stop (this blocks execution of your script, effectively making this synchronous):

```php
    $process->wait();
```

Get the process id (throws a ```\RuntimeException``` if the process has ended):

```php
    $process->getPid();
```

Read output from a stream:

```php
    $stdOut = $process->readStream(ProcessInterface::STDOUT);
```

Get the exit code (throws a ```\RuntimeException``` if the process is still running):

```php
    $exitCode = $process->getExitCode();
```

Send a signal (SIGTERM or SIGKILL) to the process:

```php
    $process->sendSignal(ProcessInterface::SIGTERM);
```

Get the string representation of the running command:

```php
    $commandString = $process->getCommand();
```



## Mocking

Mock implementations of the Command & Builder interfaces are provided to aid testing.

By type hinting against the interfaces, rather than the concrete implementations, these mocks can be injected & used to return pre-configured result objects.


## Contributing

You can contribute by submitting an Issue to the [issue tracker](https://github.com/ptlis/shell-command/issues), improving the documentation or submitting a pull request. For pull requests i'd prefer that the code style and test coverage is maintained, but I am happy to work through any minor issues that may arise so that the request can be merged.




## Known limitations

* Currently supports UNIX environments only.
