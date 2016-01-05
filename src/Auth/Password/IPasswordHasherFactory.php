<?php

namespace Dms\Web\Laravel\Auth\Password;

use Dms\Core\Auth\IHashedPassword;
use Dms\Core\Exception;

interface IPasswordHasherFactory
{
    /**
     * Builds the default password hasher.
     *
     * @return IPasswordHasher
     */
    public function buildDefault();

    /**
     * Builds a password hasher with the supplied settings
     *
     * @param string $algorithm
     * @param int    $costFactor
     *
     * @return IPasswordHasher
     * @throws Exception\InvalidArgumentException
     */
    public function build($algorithm, $costFactor);

    /**
     * Builds a password hasher matching the supplied hashed password
     *
     * @param IHashedPassword $hashedPassword
     *
     * @return IPasswordHasher
     * @throws Exception\InvalidArgumentException
     */
    public function buildFor(IHashedPassword $hashedPassword);
}
