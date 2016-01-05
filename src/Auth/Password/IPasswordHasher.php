<?php

namespace Dms\Web\Laravel\Auth\Password;

use Dms\Core\Auth\IHashedPassword;

interface IPasswordHasher
{
    /**
     * Gets the hashing algorithm name.
     *
     * @return string
     */
    public function getAlgorithm();

    /**
     * Gets the cost factor of the hashing algorithm.
     *
     * @return int
     */
    public function getCostFactor();

    /**
     * Hashes the supplied password.
     *
     * @param string $password
     *
     * @return IHashedPassword
     */
    public function hash($password);

    /**
     * Verifies the password string against the supplied hashed password.
     *
     * @param string          $password
     * @param IHashedPassword $hashedPassword
     *
     * @return boolean
     */
    public function verify($password, IHashedPassword $hashedPassword);
}
