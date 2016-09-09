<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Auth\Module;

use Dms\Common\Structure\Field;
use Dms\Core\Auth\IAdmin;
use Dms\Core\Auth\IAdminRepository;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IRoleRepository;
use Dms\Core\Common\Crud\CrudModule;
use Dms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Dms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Dms\Core\Language\Message;
use Dms\Core\Model\EntityIdCollection;
use Dms\Web\Laravel\Auth\Admin;
use Dms\Web\Laravel\Auth\Password\IPasswordHasherFactory;
use Dms\Web\Laravel\Auth\Password\IPasswordResetService;
use Dms\Web\Laravel\Auth\Role;

/**
 * The admin crud module.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AdminUserModule extends CrudModule
{
    /**
     * @var IAdminRepository
     */
    protected $dataSource;

    /**
     * @var IRoleRepository
     */
    private $roleRepo;

    /**
     * @var IPasswordHasherFactory
     */
    private $hasher;

    /**
     * @var IPasswordResetService
     */
    private $passwordResetService;

    /**
     * UserModule constructor.
     *
     * @param IAdminRepository       $dataSource
     * @param IRoleRepository        $roleRepo
     * @param IPasswordHasherFactory $hasher
     * @param IAuthSystem            $authSystem
     * @param IPasswordResetService  $passwordResetService
     */
    public function __construct(
        IAdminRepository $dataSource,
        IRoleRepository $roleRepo,
        IPasswordHasherFactory $hasher,
        IAuthSystem $authSystem,
        IPasswordResetService $passwordResetService
    )
    {
        $this->roleRepo             = $roleRepo;
        $this->hasher               = $hasher;
        $this->passwordResetService = $passwordResetService;
        parent::__construct($dataSource, $authSystem);
    }

    /**
     * Defines the structure of this module.
     *
     * @param CrudModuleDefinition $module
     */
    protected function defineCrudModule(CrudModuleDefinition $module)
    {
        $module->name('users');

        $module->metadata([
            'icon' => 'users'
        ]);

        $module->labelObjects()->fromProperty(Admin::FULL_NAME);

        $module->crudForm(function (CrudFormDefinition $form) {
            $form->section('Details', [
                $form->field(
                    AdminProfileFields::buildFullNameField($this->dataSource)
                )->bindToProperty(Admin::FULL_NAME),
                //
                $form->field(
                    AdminProfileFields::buildUsernameField($this->dataSource)
                )->bindToProperty(Admin::USERNAME),
                //
                $form->field(
                    AdminProfileFields::buildEmailField($this->dataSource)
                )->bindToProperty(Admin::EMAIL_ADDRESS),
            ]);

            if ($form->isCreateForm()) {
                $form->section('Password', [
                    //
                    $form->field(
                        Field::create('password', 'Password')
                            ->string()
                            ->password()
                            ->required()
                            ->minLength(6)
                    )->withoutBinding(),
                ]);

                $form->onSubmit(function (Admin $user, array $input) {
                    $user->setPassword($this->hasher->buildDefault()->hash($input['password']));
                });
            }

            $form->section('Access Settings', [
                //
                $form->field(
                    Field::create('is_banned', 'Is Banned?')->bool()
                )->bindToProperty(Admin::IS_BANNED),
                //
                $form->field(
                    Field::create('is_super_user', 'Is Super Admin?')->bool()
                )->bindToProperty(Admin::IS_SUPER_USER),
                //
                $form->field(
                    Field::create('roles', 'Roles')
                        ->entityIdsFrom($this->roleRepo)
                        ->mapToCollection(EntityIdCollection::type())
                        ->labelledBy(Role::NAME)
                )->bindToProperty(Admin::ROLE_IDS),
            ]);
        });

        $module->objectAction('reset-password')
            ->authorize(self::EDIT_PERMISSION)
            ->form(new AdminPasswordResetForm())
            ->returns(Message::class)
            ->handler(function (IAdmin $user, AdminPasswordResetForm $input) {
                $this->passwordResetService->resetUserPassword($user, $input->newPassword);

                return new Message('auth.user.password-reset');
            });

        $module->removeAction()->deleteFromDataSource();

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->mapProperty(Admin::USERNAME)->to(Field::create('username', 'Username')->string());
            $table->mapProperty(Admin::EMAIL_ADDRESS)->to(Field::create('email', 'Email')->email());
            $table->mapProperty(Admin::IS_SUPER_USER)->to(Field::create('super_admin', 'Super Admin')->bool());
            $table->mapProperty(Admin::IS_BANNED)->to(Field::create('banned', 'Banned')->bool());

            $table->view('all', 'All')
                ->asDefault()
                ->loadAll()
                ->orderByAsc(Admin::USERNAME);
        });

        $module->widget('summary-table')
            ->label('Accounts')
            ->withTable(self::SUMMARY_TABLE)
            ->allRows();
    }
}