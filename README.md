# ptlis/shell-command

A basic object-oriented wrapper around execution of shell commands.

The motivation behind this package was to provide a mockable API to support writing tests for dependant code. 


[![Build Status](https://travis-ci.org/ptlis/shell-command.png?branch=master)](https://travis-ci.org/ptlis/shell-command) [![Code Coverage](https://scrutinizer-ci.com/g/ptlis/shell-command/badges/coverage.png?s=6c30a32e78672ae0d7cff3ecf00ceba95049879a)](https://scrutinizer-ci.com/g/ptlis/shell-command/) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ptlis/shell-command/badges/quality-score.png?s=b8a262b33dd4a5de02d6f92f3e318ebb319f96c0)](https://scrutinizer-ci.com/g/ptlis/shell-command/) [![Latest Stable Version](https://poser.pugx.org/ptlis/shell-command/v/stable.png)](https://packagist.org/packages/ptlis/shell-command)



## Install

Either from the console:

```shell
    $ composer require ptlis/shell-command:"~0.2"
```

Or by Editing composer.json:

```javascript
    {
        "require": {
            ...
            "ptlis/shell-command": "~0.2",
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


#### Add Binary

First we must provide the 'binary' to execute:

```php
    $builder->setBinary('git');             // Binary in $PATH
    $builder->setBinary('./local/bin/git'); // Relative to current working directory
    $builder->setBinary('/usr/bin/gi');     // Fully qualified path to binary
```

If the binary is not locatable an ```InvalidBinaryException``` is thrown.



#### Add Arguments

Next we must provide any command-line arguments to the binary.


##### Ad Hoc Arguments

The simplest method is to use ad-hoc arguments, this is also useful when executing with pre-formatted arguments. 

```php
    $builder->addAdHoc(
        $command            // Eg '--foo=bar baz'
    );
```
    
##### Add Flag

Add a flag. These are usually single characters, prefixed with a '-' symbol, optionally with a value

```php
    $builder->addFlag(
        $flag,              // Eg 'l'
        $value              // (optional) Eg '50'
    );
    // Builds to '-l 50'
```
    
##### Add Argument

Add an argument. These are textual identifiers, prefixed with '--', optionally with a value. The separator is configurable & defaults to a single space character.

```php
    $builder->addFlag(
        $flag,              // Eg 'foo'
        $value,             // (optional) Eg 'bar'
        $separator          // Eg '='
    );
    // Builds to '--foo=bar'
```

##### Add Parameter

A simple string that is contextually understood by the underlying binary.

```php    
    $builder->addParameter(
        $parameter          // Eg '/path/to/my/files'
    );
```



#### Build the Command

One the builder has been configured, the command can be retrieved for execution:

```php
    $command = $builder->getCommand();
```



### Execute the Command

Executing the command is done using the ```run``` method which returns a class implementing the ```CommandResultInterface```, ```ShellResult``` by default.

```php
    $result = $command->run(); 
```

The exit code & output of the command are available as methods on this object:

```php
    $result->getExitCode();     // 0 for success, anything else conventionally indicates an error
    $result->getOutput();       // The contents of stdout
```



## Mocking

Mock implementations of the Command, Result & Builder interfaces are provided to aid testing.

By type hinting against the interfaces, rather than the concrete implementations, these mocks can be injected & used to return pre-configured result objects.


## Contributing

You can contribute by submitting an Issue to the [issue tracker](https://github.com/ptlis/shell-command/issues), improving the documentation or submitting a pull request. For pull requests i'd prefer that the code style and test coverage is maintained, but I am happy to work through any minor issues that may arise so that the request can be merged.




## Known limitations

* Currently supports UNIX environments only, pull requests welcomed for Windows (or other platforms).
