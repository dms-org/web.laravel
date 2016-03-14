<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Auth;

use Dms\Common\Structure\Web\EmailAddress;
use Dms\Core\Auth\IHashedPassword;
use Dms\Core\Auth\IAdmin;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Web\Laravel\Auth\Password\HashedPassword;
use Dms\Web\Laravel\Auth\Persistence\Mapper\AdminMapper;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;

/**
 * The laravel admin entity.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Admin extends Entity implements IAdmin, Authenticatable, CanResetPassword
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
        string $username,
        IHashedPassword $password,
        bool $isSuperUser = false,
        bool $isBanned = false,
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

        $class->property($this->roleIds)->asType(EntityIdCollection::type());

        $class->property($this->rememberToken)->nullable()->asString();
    }

    /**
     * @return EmailAddress
     */
    public function getEmailAddressObject() : EmailAddress
    {
        return $this->emailAddress;
    }

    /**
     * @return string
     */
    public function getEmailAddress() : string
    {
        return $this->emailAddress->asString();
    }

    /**
     * @return string
     */
    public function getUsername() : string
    {
        return $this->username;
    }

    /**
     * @param EmailAddress $emailAddress
     */
    public function setEmailAddress(EmailAddress $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return IHashedPassword
     */
    public function getPassword() : IHashedPassword
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
    public function isSuperUser() : bool
    {
        return $this->isSuperUser;
    }

    /**
     * @return boolean
     */
    public function isBanned() : bool
    {
        return $this->isBanned;
    }

    /**
     * @return EntityIdCollection
     */
    public function getRoleIds() : EntityIdCollection
    {
        return $this->roleIds;
    }

    /**
     * @param Role $role
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function giveRole(Role $role)
    {
        if (!$this->hasId()) {
            throw InvalidOperationException::format('The user must have an id');
        }

        if (!$role->hasId()) {
            throw InvalidOperationException::format('The supplied role must have an id');
        }

        if (!$this->roleIds->contains($role->getId())) {
            $this->roleIds[] = $role->getId();
        }

        if (!$role->getUserIds()->contains($this->getId())) {
            $role->getUserIds()[] = $this->getId();
        }
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName() : string
    {
        return AdminMapper::AUTH_IDENTIFIER_COLUMN;
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
    public function getAuthPassword() : string
    {
        return AdminMapper::AUTH_PASSWORD_COLUMN;
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
        return AdminMapper::AUTH_REMEMBER_TOKEN_COLUMN;
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->emailAddress->asString();
    }
}