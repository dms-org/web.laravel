<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Cms\Modules\Fields;

use Dms\Core\Common\Crud\Definition\Form\ValueObjectFieldDefinition;
use Dms\Core\Common\Crud\Form\ValueObjectField;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestDateTimeRangeValueObject;
use Dms\Common\Structure\Field;

/**
 * The Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestDateTimeRangeValueObject value object field.
 */
class TestDateTimeRangeValueObjectField extends ValueObjectField
{


    public function __construct(string $name, string $label)
    {

        parent::__construct($name, $label);
    }

    /**
     * Defines the structure of this value object field.
     *
     * @param ValueObjectFieldDefinition $form
     *
     * @return void
     */
    protected function define(ValueObjectFieldDefinition $form)
    {
        $form->bindTo(TestDateTimeRangeValueObject::class);

        $form->section('Details', [
            $form->field(
                /* TODO: TestDateTimeRangeValueObject::DATE_TIME_RANGE */
            )->bindToProperty(TestDateTimeRangeValueObject::DATE_TIME_RANGE),
            //
            $form->field(
                /* TODO: TestDateTimeRangeValueObject::NULLABLE_DATE_TIME_RANGE */
            )->bindToProperty(TestDateTimeRangeValueObject::NULLABLE_DATE_TIME_RANGE),
            //
            $form->field(
                /* TODO: TestDateTimeRangeValueObject::DATE_RANGE */
            )->bindToProperty(TestDateTimeRangeValueObject::DATE_RANGE),
            //
            $form->field(
                /* TODO: TestDateTimeRangeValueObject::NULLABLE_DATE_RANGE */
            )->bindToProperty(TestDateTimeRangeValueObject::NULLABLE_DATE_RANGE),
            //
            $form->field(
                /* TODO: TestDateTimeRangeValueObject::TIME_RANGE */
            )->bindToProperty(TestDateTimeRangeValueObject::TIME_RANGE),
            //
            $form->field(
                /* TODO: TestDateTimeRangeValueObject::NULLABLE_TIME_RANGE */
            )->bindToProperty(TestDateTimeRangeValueObject::NULLABLE_TIME_RANGE),
            //
            $form->field(
                /* TODO: TestDateTimeRangeValueObject::TIMEZONED_DATE_TIME_RANGE */
            )->bindToProperty(TestDateTimeRangeValueObject::TIMEZONED_DATE_TIME_RANGE),
            //
            $form->field(
                /* TODO: TestDateTimeRangeValueObject::NULLABLE_TIMEZONED_DATE_TIME_RANGE */
            )->bindToProperty(TestDateTimeRangeValueObject::NULLABLE_TIMEZONED_DATE_TIME_RANGE),
            //
        ]);

    }
}