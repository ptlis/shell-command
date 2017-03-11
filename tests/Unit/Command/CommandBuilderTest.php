<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Unit\Environment;

use ptlis\ShellCommand\CommandBuilder;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @todo merge with ptlis\ShellCommand\Unit\CommandBuilder\CommandBuilderTest
 */
class CommandBuilderTest extends ptlisShellCommandTestcase
{
    public function testDetectEnvironmentSuccess()
    {
        $builder = new CommandBuilder();

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

        $builder = new CommandBuilder();

        $builder->getEnvironment('foobar');
    }
}
