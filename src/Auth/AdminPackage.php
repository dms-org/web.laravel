<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Auth;

use Dms\Core\Package\Definition\PackageDefinition;
use Dms\Core\Package\Package;
use Dms\Web\Laravel\Auth\Module\RoleModule;
use Dms\Web\Laravel\Auth\Module\UserModule;

/**
 * The auth package.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AdminPackage extends Package
{
    /**
     * Defines the structure of this cms package.
     *
     * @param PackageDefinition $package
     *
     * @return void
     */
    protected function define(PackageDefinition $package)
    {
        $package->name('admin');

        $package->dashboard()
            ->widgets([
                'users.summary-table',
                'roles.summary-table',
            ]);

        $package->modules([
            'users' => UserModule::class,
            'roles' => RoleModule::class,
        ]);
    }
}