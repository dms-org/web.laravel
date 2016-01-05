<?php

namespace Dms\Web\Laravel\Auth\Module;

use Dms\Common\Structure\Field;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IRoleRepository;
use Dms\Core\Auth\IUserRepository;
use Dms\Core\Auth\Permission;
use Dms\Core\Common\Crud\CrudModule;
use Dms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Dms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Dms\Core\ICms;
use Dms\Web\Laravel\Auth\Role;

/**
 * The role crud module.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RoleModule extends CrudModule
{
    /**
     * @var IUserRepository
     */
    private $userRepo;

    /**
     * @var ICms
     */
    private $cms;

    /**
     * RoleModule constructor.
     *
     * @param IRoleRepository $dataSource
     * @param IUserRepository $userRepo
     * @param IAuthSystem     $authSystem
     * @param ICms            $cms
     */
    public function __construct(IRoleRepository $dataSource, IUserRepository $userRepo, IAuthSystem $authSystem, ICms $cms)
    {
        parent::__construct($dataSource, $authSystem);
        $this->cms      = $cms;
        $this->userRepo = $userRepo;
    }

    /**
     * Defines the structure of this module.
     *
     * @param CrudModuleDefinition $module
     */
    protected function defineCrudModule(CrudModuleDefinition $module)
    {
        $module->name('roles');

        $module->labelObjects()->fromProperty(Role::NAME);

        $permissionOptions = [];

        foreach ($this->cms->loadPermissions() as $permission) {
            $permissionOptions[$permission->getName()] = ucwords(strtr($permission->getName(), ['-' => ' ', '.' => ' - ']));
        }

        $module->crudForm(function (CrudFormDefinition $form) use ($permissionOptions) {
            $form->section('Details', [
                //
                $form->field(
                        Field::create('name', 'Name')
                                ->string()
                                ->required()
                                ->maxLength(100)
                )->bindToProperty(Role::NAME),
                //
                $form->field(
                        Field::create('permissions', 'Permissions')
                                ->arrayOf(Field::element()
                                        ->string()
                                        ->oneOf($permissionOptions)
                                        ->required()
                                        ->map(function ($name) {
                                            Permission::named($name);
                                        }, function (Permission $permission) {
                                            return $permission->getName();
                                        }, Permission::type())
                                )->required()->containsNoDuplicates()->minLength(1)
                )->bindToProperty(Role::PERMISSIONS),
                //
                $form->field(
                        Field::create('users', 'Users')
                                ->entityIdsFrom($this->userRepo)
                                ->required()
                                ->containsNoDuplicates()
                )->bindToProperty(Role::USER_IDS),
            ]);
        });

        $module->removeAction()->deleteFromRepository();

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->mapProperty(Role::NAME)->to(Field::create('name', 'Name')->string());

            $table->mapProperty('count(' . Role::PERMISSIONS . ')')->to(Field::create('permissions', 'Permissions')->int());

            $table->mapProperty('count(' . Role::USER_IDS . ')')->to(Field::create('users', 'Users')->int());

            $table->view('default', 'Default')
                    ->asDefault()
                    ->loadAll()
                    ->orderByAsc(Role::NAME);
        });
    }
}