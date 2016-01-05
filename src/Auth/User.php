<?php

namespace Dms\Web\Laravel\Auth;

use Dms\Common\Structure\Web\EmailAddress;
use Dms\Core\Auth\IHashedPassword;
use Dms\Core\Auth\IUser;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Web\Laravel\Auth\Password\HashedPassword;
use Dms\Web\Laravel\Auth\Persistence\Mapper\UserMapper;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * The laravel user entity.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class User extends Entity implements IUser, Authenticatable
{
    const EMAIL_ADDRESS = 'emailAddress';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const IS_SUPER_USER = 'isSuperUser';
    const IS_BANNED = 'isBanned';
    const REMEMBER_TOKEN = 'rememberToken';
    const ROLE_IDS = 'roleIds';

    /**
     * @var EmailAddress
     */
    protected $emailAddress;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var HashedPassword
     */
    protected $password;

    /**
     * @var bool
     */
    protected $isSuperUser;

    /**
     * @var bool
     */
    protected $isBanned;

    /**
     * @var string|null
     */
    protected $rememberToken;

    /**
     * @var EntityIdCollection
     */
    protected $roleIds;

    /**
     * LaravelUser constructor.
     *
     * @param EmailAddress            $emailAddress
     * @param string                  $username
     * @param IHashedPassword         $password
     * @param bool                    $isSuperUser
     * @param bool                    $isBanned
     * @param EntityIdCollection|null $roleIds
     */
    public function __construct(
            EmailAddress $emailAddress,
            $username,
            IHashedPassword $password,
            $isSuperUser = false,
            $isBanned = false,
            EntityIdCollection $roleIds = null
    ) {
        parent::__construct();

        $this->emailAddress = $emailAddress;
        $this->username     = $username;
        $this->password     = HashedPassword::from($password);
        $this->isSuperUser  = $isSuperUser;
        $this->isBanned     = $isBanned;
        $this->roleIds      = $roleIds ?: new EntityIdCollection();

        InvalidArgumentException::verify(strlen($this->username) > 0, 'Username cannot be empty');
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->emailAddress)->asObject(EmailAddress::class);

        $class->property($this->username)->asString();

        $class->property($this->password)->asObject(HashedPassword::class);

        $class->property($this->isSuperUser)->asBool();

        $class->property($this->isBanned)->asBool();

        $class->property($this->roleIds)->asObject(EntityIdCollection::class);

        $class->property($this->rememberToken)->nullable()->asString();
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress->asString();
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return HashedPassword
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param IHashedPassword $password
     */
    public function setPassword(IHashedPassword $password)
    {
        $this->password = $password;
    }

    /**
     * @return boolean
     */
    public function isSuperUser()
    {
        return $this->isSuperUser;
    }

    /**
     * @return boolean
     */
    public function isBanned()
    {
        return $this->isBanned;
    }

    /**
     * @return EntityIdCollection
     */
    public function getRoleIds()
    {
        return $this->roleIds;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return UserMapper::AUTH_IDENTIFIER_COLUMN;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->username;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return UserMapper::AUTH_PASSWORD_COLUMN;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->rememberToken;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->rememberToken = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return UserMapper::AUTH_REMEMBER_TOKEN_COLUMN;
    }
}