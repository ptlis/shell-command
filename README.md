# ptlis/shell-command

A developer-friendly wrapper around execution of shell commands.

The motivation behind this package was twofold:
* To provide easily mockable interfaces & ship default mocks - supporting the writing tests for dependant code.
* To provide interfaces that neatly encapsulate state - something that I felt was lacking in existing packages.


[![Build Status](https://travis-ci.org/ptlis/shell-command.png?branch=master)](https://travis-ci.org/ptlis/shell-command) [![Code Coverage](https://scrutinizer-ci.com/g/ptlis/shell-command/badges/coverage.png?s=6c30a32e78672ae0d7cff3ecf00ceba95049879a)](https://scrutinizer-ci.com/g/ptlis/shell-command/) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ptlis/shell-command/badges/quality-score.png?s=b8a262b33dd4a5de02d6f92f3e318ebb319f96c0)](https://scrutinizer-ci.com/g/ptlis/shell-command/) [![Latest Stable Version](https://poser.pugx.org/ptlis/shell-command/v/stable.png)](https://packagist.org/packages/ptlis/shell-command)


## Install

Either from the console:

```shell
    $ composer require ptlis/shell-command:"~0.6"
```

Or by Editing composer.json:

```javascript
    {
        "require": {
            ...
            "ptlis/shell-command": "~0.6",
            ...
        }
    }
```

Followed by a composer update:

```shell
    $ composer update
```



## Usage

### The Builder

The package ships with a command builder, providing a simple and safe method to build commands. 

```php
    use ptlis\ShellCommand\ShellCommandBuilder;
    
    $builder = new ShellCommandBuilder();
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



#### Add Arguments

Next we may provide any arguments to the command, either chained:

```php
    $builder
        ->addArgument('--foo=bar')
        ->addArgument('-xzcf')
        ->addArgument('if=/dev/sda of=/dev/sdb')
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


#### Build the Command

One the builder has been configured, the command can be retrieved for execution:

```php
    $command = $builder
        // Command configuration...
        ->buildCommand();
```



### Synchronous Execution

Executing the command is done using the ```runSynchronous``` method which returns an object implementing the ```CommandResultInterface```.

```php
    $result = $command->runSynchronous(); 
```

The exit code & output of the command are available as methods on this object:

```php
    $result->getExitCode();     // 0 for success, anything else conventionally indicates an error
    $result->getStdOut();       // The contents of stdout (as a string)
    $result->getStdOutLines();  // The contents of stdout (as an array of lines)
    $result->getStdErr();       // The contents of stderr (as a string)
    $result->getStdErrLines();  // The contents of stderr (as an array of lines)
```



## Mocking

Mock implementations of the Command & Builder interfaces are provided to aid testing.

By type hinting against the interfaces, rather than the concrete implementations, these mocks can be injected & used to return pre-configured result objects.


## Contributing

You can contribute by submitting an Issue to the [issue tracker](https://github.com/ptlis/shell-command/issues), improving the documentation or submitting a pull request. For pull requests i'd prefer that the code style and test coverage is maintained, but I am happy to work through any minor issues that may arise so that the request can be merged.




## Known limitations

* Currently supports UNIX environments only, pull requests welcomed for Windows (or other platforms).
