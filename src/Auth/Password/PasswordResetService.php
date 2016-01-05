<?php

namespace Dms\Web\Laravel\Auth\Password;

use Dms\Core\Auth\IUser;
use Dms\Core\Auth\IUserRepository;

/**
 * The password reset service
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PasswordResetService implements IPasswordResetService
{
    /**
     * @var IUserRepository
     */
    private $userRepository;

    /**
     * @var IPasswordHasherFactory
     */
    protected $hasherFactory;

    /**
     * PasswordResetService constructor.
     *
     * @param IUserRepository        $userRepository
     * @param IPasswordHasherFactory $hasherFactory
     */
    public function __construct(IUserRepository $userRepository, IPasswordHasherFactory $hasherFactory)
    {
        $this->userRepository = $userRepository;
        $this->hasherFactory  = $hasherFactory;
    }

    /**
     * Resets the user's password.
     *
     * @param IUser  $user
     * @param string $newPassword
     *
     * @return void
     */
    public function resetUserPassword(IUser $user, $newPassword)
    {
        $hashedPassword = $this->hasherFactory->buildDefault()->hash($newPassword);

        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);
    }
}