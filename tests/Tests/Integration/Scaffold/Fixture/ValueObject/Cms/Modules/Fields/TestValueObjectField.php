<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Cms\Modules\Fields;

use Dms\Core\Common\Crud\Definition\Form\ValueObjectFieldDefinition;
use Dms\Core\Common\Crud\Form\ValueObjectField;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestValueObject;

/**
 * The Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestValueObject value object field.
 */
class TestValueObjectField extends ValueObjectField
{
    /**
     * Defines the structure of this value object field.
     *
     * @param ValueObjectFieldDefinition $form
     *
     * @return void
     */
    protected function define(ValueObjectFieldDefinition $form)
    {
        $form->bindTo(TestValueObject::class);

        $form->section('', [
            // TODO
        ]);
    }
}