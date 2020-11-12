<?php declare(strict_types = 1);

namespace Database\Seeders;

use Dms\Common\Structure\Web\EmailAddress;
use Dms\Core\Auth\IAdminRepository;
use Dms\Web\Laravel\Auth\Password\IPasswordHasherFactory;
use Dms\Web\Laravel\Auth\LocalAdmin;
use Illuminate\Database\Seeder;

/**
 * The DMS admin account seeder
 */
class DmsAdminSeeder extends Seeder
{
    /**
     * @var IAdminRepository
     */
    protected $repo;

    /**
     * @var IPasswordHasherFactory
     */
    protected $hasherFactory;

    /**
     * DmsUserSeeder constructor.
     *
     * @param IAdminRepository        $repo
     * @param IPasswordHasherFactory $hasherFactory
     */
    public function __construct(IAdminRepository $repo, IPasswordHasherFactory $hasherFactory)
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
        $this->repo->clear();

        $this->repo->save(new LocalAdmin(
            'Admin',
            new EmailAddress('admin@admin.com'),
            'admin',
            $this->hasherFactory->buildDefault()->hash('admin'),
            true // super user
        ));
    }
}