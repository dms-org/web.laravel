<?php

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Common\Structure\DateTime\Form\DateOrTimeRangeType;
use Dms\Core\Form\Field\Type\DateTimeTypeBase;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

/**
 * The date/time range field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateOrTimeRangeFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass()
    {
        return DateOrTimeRangeType::class;
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return bool
     */
    protected function canRender(IField $field, IFieldType $fieldType)
    {
        return !$fieldType->has(FieldType::ATTR_OPTIONS);
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderField(IField $field, IFieldType $fieldType)
    {
        return $this->renderView(
                $field,
                'dms::components.form.field.date-or-time.range.input',
                [
                        DateTimeTypeBase::ATTR_FORMAT => 'format',
                        DateTimeTypeBase::ATTR_MIN    => 'min',
                        DateTimeTypeBase::ATTR_MAX    => 'max',
                        // TODO: less_than and greater_than
                ]
        );
    }
}