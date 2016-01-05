<?php

use Dms\Common\Structure\Web\EmailAddress;
use Dms\Core\Auth\IUserRepository;
use Dms\Web\Laravel\Auth\Password\IPasswordHasherFactory;
use Dms\Web\Laravel\Auth\User;
use Illuminate\Database\Seeder;

/**
 * The dms user seeder
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DmsUserSeeder extends Seeder
{
    /**
     * @var IUserRepository
     */
    protected $repo;

    /**
     * @var IPasswordHasherFactory
     */
    private $hasherFactory;

    /**
     * DmsUserSeeder constructor.
     *
     * @param IUserRepository        $repo
     * @param IPasswordHasherFactory $hasherFactory
     */
    public function __construct(IUserRepository $repo, IPasswordHasherFactory $hasherFactory)
    {
        $this->repo          = $repo;
        $this->hasherFactory = $hasherFactory;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->repo->save(new User(
                new EmailAddress('admin@admin.com'),
                'admin',
                $this->hasherFactory->buildDefault()->hash('admin'),
                true // super user
        ));
    }
}