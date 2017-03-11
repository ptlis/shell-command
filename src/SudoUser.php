<?php

/**
 * @copyright (c) 2015-2017 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

/**
 * Stores a user's username & password to use with sudo
 */
class SudoUser
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;


    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(
        $username,
        $password
    ) {
        $this->username = $username;
        $this->password = $password;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
