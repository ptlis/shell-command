<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit\Environment;

use ptlis\ShellCommand\ShellCommandBuilder;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

class CommandBuilderTest extends ptlisShellCommandTestcase
{
    public function testDetectEnvironmentSuccess()
    {
        $builder = new ShellCommandBuilder();

        $environment = $builder->getEnvironment('Linux');

        $this->assertEquals(
            new UnixEnvironment(),
            $environment
        );
    }

    public function testDetectEnvironmentError()
    {
        $this->setExpectedException(
            '\RuntimeException',
            'Unable to find Environment for OS "foobar".'
        );

        $builder = new ShellCommandBuilder();

        $builder->getEnvironment('foobar');
    }
}
