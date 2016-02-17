<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Auth;

use Dms\Core\Auth\IUser;
use Dms\Core\Auth\IUserRepository;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Web\Laravel\Auth\Password\IPasswordHasherFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * The custom user provider.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DmsUserProvider implements UserProvider
{
    /**
     * @var IUserRepository
     */
    protected $repository;

    /**
     * @var IPasswordHasherFactory
     */
    protected $passwordHasherFactory;

    /**
     * DmsUserProvider constructor.
     *
     * @param IUserRepository        $repository
     * @param IPasswordHasherFactory $passwordHasherFactory
     */
    public function __construct(IUserRepository $repository, IPasswordHasherFactory $passwordHasherFactory)
    {
        $this->repository            = $repository;
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $username
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($username)
    {
        $users = $this->repository->matching(
            $this->repository->criteria()
                ->where(User::USERNAME, '=', $username)
        );

        return reset($users) ?: null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $username
     * @param  string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($username, $token)
    {
        $users = $this->repository->matching(
            $this->repository->criteria()
                ->where(User::USERNAME, '=', $username)
                ->where(User::REMEMBER_TOKEN, '=', $token)
        );

        return reset($users) ?: null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $token
     *
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user = $this->validateUser($user);

        $user->setRememberToken($token);
        $this->repository->save($user);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $criteria = $this->criteriaFromCredentialsArray($credentials);

        $users = $this->repository->matching($criteria);

        return reset($users) ?: null;
    }

    /**
     * @param array $credentials
     *
     * @return \Dms\Core\Model\Criteria\Criteria
     */
    private function criteriaFromCredentialsArray(array $credentials)
    {
        $criteria = $this->repository->criteria();

        foreach ($credentials as $column => $value) {
            if (strpos($column, 'password') === false) {
                $criteria->where($column, '=', $value);
            }
        }

        return $criteria;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials) : bool
    {
        $user = $this->validateUser($user);

        $passwordHasher = $this->passwordHasherFactory->buildFor($user->getPassword());

        return $passwordHasher->verify($credentials['password'], $user->getPassword());
    }

    /**
     * @param Authenticatable $user
     *
     * @return IUser|Authenticatable
     * @throws TypeMismatchException
     */
    private function validateUser(Authenticatable $user)
    {
        if (!($user instanceof IUser)) {
            throw TypeMismatchException::format('Expecting instance of %s, %s given', IUser::class, get_class($user));
        }

        return $user;
    }
}