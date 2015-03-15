<?php

/**
 * PHP Version 5.3
 *
 * @copyright   (c) 2015 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ptlis\ShellCommand\Interfaces\ProcessObserverInterface;

/**
 * Base-class for logging, simply proxying to the decorated class for all implemented methods.
 */
abstract class BaseProcessLogger implements ProcessObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $logLevel;


    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param string $logLevel One of Psr\Log\LogLevel class constants.
     */
    public function __construct(LoggerInterface $logger, $logLevel = LogLevel::DEBUG) {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    /**
     * Create a log entry, utility method for derived classes.
     *
     * @param string $message
     * @param array $context
     */
    protected function log($message, array $context = array())
    {
        $this->logger->log($this->logLevel, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function processCreated($command)
    {
        // Do nothing, convenience method so that derived classes only need to implement relevant one.
    }

    /**
     * {@inheritDoc}
     */
    public function stdOutRead($stdOut)
    {
        // Do nothing, convenience method so that derived classes only need to implement relevant one.
    }

    /**
     * {@inheritDoc}
     */
    public function stdErrRead($stdErr)
    {
        // Do nothing, convenience method so that derived classes only need to implement relevant one.
    }

    /**
     * {@inheritDoc}
     */
    public function sentSignal($signal)
    {
        // Do nothing, convenience method so that derived classes only need to implement relevant one.
    }

    /**
     * {@inheritDoc}
     */
    public function processExited($exitCode)
    {
        // Do nothing, convenience method so that derived classes only need to implement relevant one.
    }
}
