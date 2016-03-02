<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Auth\Module;

use Dms\Common\Structure\Field;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IRoleRepository;
use Dms\Core\Auth\IUser;
use Dms\Core\Auth\IUserRepository;
use Dms\Core\Common\Crud\CrudModule;
use Dms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Dms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Language\Message;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\Object\ArrayDataObject;
use Dms\Web\Laravel\Auth\Password\IPasswordHasherFactory;
use Dms\Web\Laravel\Auth\Password\IPasswordResetService;
use Dms\Web\Laravel\Auth\Role;
use Dms\Web\Laravel\Auth\User;

/**
 * The user crud module.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UserModule extends CrudModule
{
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
     * @param IUserRepository        $dataSource
     * @param IRoleRepository        $roleRepo
     * @param IPasswordHasherFactory $hasher
     * @param IAuthSystem            $authSystem
     * @param IPasswordResetService  $passwordResetService
     */
    public function __construct(
        IUserRepository $dataSource,
        IRoleRepository $roleRepo,
        IPasswordHasherFactory $hasher,
        IAuthSystem $authSystem,
        IPasswordResetService $passwordResetService
    ) {
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

        $module->labelObjects()->fromCallback(function (User $user) {
            return $user->getUsername() . ' <' . $user->getEmailAddress() . '>';
        });

        $module->crudForm(function (CrudFormDefinition $form) {
            $form->section('Details', [
                //
                $form->field(
                    Field::create('username', 'Username')
                        ->string()
                        ->required()
                        ->uniqueIn($this->dataSource, User::USERNAME)
                        ->maxLength(100)
                )->bindToProperty(User::USERNAME),
                //
                $form->field(
                    Field::create('email', 'Email Address')
                        ->email()
                        ->required()
                        ->uniqueIn($this->dataSource, User::EMAIL_ADDRESS)
                        ->maxLength(100)
                )->bindToProperty(User::EMAIL_ADDRESS),
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

                $form->onSubmit(function (User $user, array $input) {
                    $user->setPassword($this->hasher->buildDefault()->hash($input['password']));
                });
            }

            $form->section('Access Settings', [
                //
                $form->field(
                    Field::create('is_banned', 'Is Banned?')->bool()
                )->bindToProperty(User::IS_BANNED),
                //
                $form->field(
                    Field::create('is_super_user', 'Is Super Admin?')->bool()
                )->bindToProperty(User::IS_SUPER_USER),
                //
                $form->field(
                    Field::create('roles', 'Roles')
                        ->entityIdsFrom($this->roleRepo)
                        ->mapToCollection(EntityIdCollection::type())
                        ->labelledBy(Role::NAME)
                        ->required()
                        ->minLength(1)
                )->bindToProperty(User::ROLE_IDS),
            ]);
        });

        $module->objectAction('reset-password')
            ->authorize(self::EDIT_PERMISSION)
            ->form(
                Form::create()
                    ->section('Details', [
                        Field::create('new_password', 'New Password')
                            ->string()
                            ->password()
                            ->minLength(6)
                            ->maxLength(50)
                            ->required(),
                        Field::create('new_password_confirmation', 'Confirm Password')
                            ->string()
                            ->password()
                            ->required(),
                    ])
                    ->fieldsMatch('new_password', 'new_password_confirmation')
            )
            ->returns(Message::class)
            ->handler(function (IUser $user, ArrayDataObject $input) {
                $this->passwordResetService->resetUserPassword($user, $input['new_password']);

                return new Message('auth.user.password-reset');
            });

        $module->removeAction()->deleteFromDataSource();

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->mapProperty(User::USERNAME)->to(Field::create('username', 'Username')->string());
            $table->mapProperty(User::EMAIL_ADDRESS)->to(Field::create('email', 'Email')->email());
            $table->mapProperty(User::IS_SUPER_USER)->to(Field::create('super_admin', 'Super Admin')->bool());
            $table->mapProperty(User::IS_BANNED)->to(Field::create('banned', 'Banned')->bool());

            $table->view('all', 'All')
                ->asDefault()
                ->loadAll()
                ->orderByAsc(User::USERNAME);
        });

        $module->widget('summary-table')
            ->label('Accounts')
            ->withTable(self::SUMMARY_TABLE)
            ->allRows();
    }
}