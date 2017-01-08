<?php

require_once 'vendor/autoload.php';

use ptlis\ShellCommand\CommandBuilder;

$builder = new CommandBuilder();

$command = $builder
    ->setSudo(true, 'lomh[om1985')
    ->setCommand('touch')
    ->addArgument('foo')
    ->buildCommand();

echo $command . PHP_EOL;

var_dump($command->runSynchronous());