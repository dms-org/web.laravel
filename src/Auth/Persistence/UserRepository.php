<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Auth\Persistence;

use Dms\Core\Auth\IUserRepository;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\DbRepository;
use Dms\Web\Laravel\Auth\User;

/**
 * The laravel user repository.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UserRepository extends DbRepository implements IUserRepository
{
    public function __construct(IConnection $connection, IOrm $orm)
    {
        parent::__construct($connection, $orm->getEntityMapper(User::class));
    }
}