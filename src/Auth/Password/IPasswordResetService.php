<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Auth\Password;

use Dms\Core\Auth\IUser;

/**
 * The password reset service interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IPasswordResetService
{
    /**
     * Resets the user's password.
     *
     * @param IUser  $user
     * @param string $newPassword
     *
     * @return void
     */
    public function resetUserPassword(IUser $user, string $newPassword);
}