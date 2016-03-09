<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Auth\Password;

use Dms\Core\Auth\IAdmin;
use Dms\Core\Auth\IAdminRepository;

/**
 * The password reset service
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PasswordResetService implements IPasswordResetService
{
    /**
     * @var IAdminRepository
     */
    private $userRepository;

    /**
     * @var IPasswordHasherFactory
     */
    protected $hasherFactory;

    /**
     * PasswordResetService constructor.
     *
     * @param IAdminRepository        $userRepository
     * @param IPasswordHasherFactory $hasherFactory
     */
    public function __construct(IAdminRepository $userRepository, IPasswordHasherFactory $hasherFactory)
    {
        $this->userRepository = $userRepository;
        $this->hasherFactory = $hasherFactory;
    }

    /**
     * Resets the user's password.
     *
     * @param IAdmin  $user
     * @param string $newPassword
     *
     * @return void
     */
    public function resetUserPassword(IAdmin $user, string $newPassword)
    {
        $hashedPassword = $this->hasherFactory->buildDefault()->hash($newPassword);

        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);
    }
}