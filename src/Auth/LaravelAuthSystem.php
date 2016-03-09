<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Auth;

use Dms\Core\Auth\AdminBannedException;
use Dms\Core\Auth\AdminForbiddenException;
use Dms\Core\Auth\IAdmin;
use Dms\Core\Auth\IAdminRepository;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\InvalidCredentialsException;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\IRoleRepository;
use Dms\Core\Auth\NotAuthenticatedException;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Web\Laravel\Auth\Password\IPasswordHasherFactory;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\StatefulGuard;

/**
 * The auth system implementation using the laravel auth component.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LaravelAuthSystem implements IAuthSystem
{
    /**
     * @var StatefulGuard
     */
    protected $laravelAuth;

    /**
     * @var IAdminRepository
     */
    protected $userRepository;

    /**
     * @var IRoleRepository
     */
    protected $roleRepository;

    /**
     * @var IPasswordHasherFactory
     */
    protected $passwordHasherFactory;

    /**
     * @var IPermission[]|null
     */
    protected $currentUsersPermissions;

    /**
     * LaravelAuthSystem constructor.
     *
     * @param AuthManager            $laravelAuth
     * @param IAdminRepository       $userRepository
     * @param IRoleRepository        $roleRepository
     * @param IPasswordHasherFactory $passwordHasherFactory
     */
    public function __construct(
        AuthManager $laravelAuth,
        IAdminRepository $userRepository,
        IRoleRepository $roleRepository,
        IPasswordHasherFactory $passwordHasherFactory
    )
    {
        $this->laravelAuth           = $laravelAuth->guard('dms');
        $this->userRepository        = $userRepository;
        $this->passwordHasherFactory = $passwordHasherFactory;
        $this->roleRepository        = $roleRepository;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return IAdmin
     * @throws InvalidCredentialsException
     * @throws AdminBannedException
     */
    protected function loadByCredentials(string $username, string $password) : IAdmin
    {
        /** @var IAdmin $user */
        $users = $this->userRepository->matching(
            $this->userRepository->criteria()
                ->where(Admin::USERNAME, '=', $username)
        );

        if (count($users) !== 1) {
            throw InvalidCredentialsException::defaultMessage($username);
        }

        $user           = $users[0];
        $passwordHasher = $this->passwordHasherFactory->buildFor($user->getPassword());

        if (!$passwordHasher->verify($password, $user->getPassword())) {
            throw InvalidCredentialsException::defaultMessage($username);
        }

        if ($user->isBanned()) {
            throw AdminBannedException::defaultMessage($user);
        }

        return $user;
    }

    /**
     * Attempts to login with the supplied credentials.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     * @throws InvalidCredentialsException
     * @throws AdminBannedException
     */
    public function login(string $username, string $password)
    {
        $user = $this->loadByCredentials($username, $password);

        $this->laravelAuth->login($user);
    }

    /**
     * Attempts to logout the currently authenticated user.
     *
     * @return void
     * @throws NotAuthenticatedException
     */
    public function logout()
    {
        $this->getAuthenticatedUser();

        $this->laravelAuth->logout();
    }

    /**
     * Resets the users credentials.
     *
     * @param string $username
     * @param string $oldPassword
     * @param string $newPassword
     *
     * @return void
     * @throws InvalidCredentialsException
     * @throws AdminBannedException
     */
    public function resetPassword(string $username, string $oldPassword, string $newPassword)
    {
        $user = $this->loadByCredentials($username, $oldPassword);

        $hashedNewPassword = $this->passwordHasherFactory->buildDefault()->hash($newPassword);
        $user->setPassword($hashedNewPassword);
        $this->userRepository->save($user);
    }

    /**
     * Returns whether there is an authenticated user.
     *
     * @return boolean
     */
    public function isAuthenticated() : bool
    {
        $user = $this->laravelAuth->user();
        return $user !== null;
    }

    /**
     * Returns the currently authenticated user.
     *
     * @return IAdmin
     * @throws NotAuthenticatedException
     */
    public function getAuthenticatedUser() : IAdmin
    {
        $user = $this->laravelAuth->user();

        if (!$user) {
            throw NotAuthenticatedException::format('No user is authenticated');
        }

        return $user;
    }

    /**
     * Returns whether the currently authenticated user has the
     * supplied permissions.
     *
     * @param IPermission[] $permissions
     *
     * @return boolean
     */
    public function isAuthorized(array $permissions) : bool
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'permissions', $permissions, IPermission::class);

        if (!$this->isAuthenticated()) {
            return false;
        }

        $user = $this->getAuthenticatedUser();

        if ($user->isBanned()) {
            return false;
        }

        if ($user->isSuperUser()) {
            return true;
        }

        $userPermissions = $this->getUserPermissions();

        foreach ($permissions as $permission) {
            if (!isset($userPermissions[$permission->getName()])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifies whether the currently authenticated user has the supplied
     * permissions.
     *
     * @param IPermission[] $permissions
     *
     * @return void
     * @throws AdminForbiddenException
     * @throws NotAuthenticatedException
     * @throws AdminBannedException
     */
    public function verifyAuthorized(array $permissions)
    {
        $user = $this->getAuthenticatedUser();

        if (!$this->isAuthorized($permissions)) {
            throw new AdminForbiddenException($user, $permissions);
        }
    }

    /**
     * @return IPermission[]
     */
    public function getUserPermissions() : array
    {
        if ($this->currentUsersPermissions === null) {
            $this->currentUsersPermissions = Role::collection(
                $this->roleRepository->getAllById($this->getAuthenticatedUser()->getRoleIds()->asArray())
            )->selectMany(function (Role $role) {
                return $role->getPermissions();
            })->indexBy(function (IPermission $permission) {
                return $permission->getName();
            })->asArray();
        }

        return $this->currentUsersPermissions;
    }
}