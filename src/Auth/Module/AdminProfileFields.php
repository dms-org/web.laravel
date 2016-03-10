<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Auth\Module;

use Dms\Common\Structure\Field;
use Dms\Core\Auth\IAdminRepository;
use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Web\Laravel\Auth\Admin;

/**
 * The admin profile form builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AdminProfileFields
{
    public static function buildUsernameField(IAdminRepository $dataSource) : FieldBuilderBase
    {
        return Field::create('username', 'Username')
            ->string()
            ->required()
            ->uniqueIn($dataSource, Admin::USERNAME)
            ->maxLength(100);
    }

    public static function buildEmailField(IAdminRepository $dataSource) : FieldBuilderBase
    {
        return Field::create('email', 'Email Address')
            ->email()
            ->required()
            ->uniqueIn($dataSource, Admin::EMAIL_ADDRESS)
            ->maxLength(100);
    }
}