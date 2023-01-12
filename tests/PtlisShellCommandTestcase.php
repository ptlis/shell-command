<?php

/**
 * @copyright (c) 2015-present brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ptlis\ShellCommand\Test;

class PtlisShellCommandTestcase extends ExpectDeprecationTestCase
{
    /**
     * @param array<mixed> $expectedLogList
     * @param array<mixed> $actualLogList
     * @return void
     */
    public function assertHasLogs(array $expectedLogList, array $actualLogList): void
    {
        foreach ($expectedLogList as $index => $expectedLog) {
            $hasMatch = false;

            foreach ($actualLogList as $actualLog) {
                if ($expectedLog == $actualLog) {
                    $hasMatch = true;
                }
            }

            $this->assertEquals(true, $hasMatch, 'Element with index ' . $index . ' not found');
        }
    }
}
