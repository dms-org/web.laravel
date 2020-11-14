<?php

namespace Dms\Web\Laravel\Tests\Integration\Fixtures\Demo;

use Illuminate\Database\Seeder;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DemoDatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        require_once __DIR__ . '/../../../../../src/Install/Stubs/DmsAdminSeeder.php.stub';
        $this->call(\Database\Seeders\DmsAdminSeeder::class);
    }
}
