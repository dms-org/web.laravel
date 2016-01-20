<?php

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\FloatType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

/**
 * The decimal field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DecimalFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass()
    {
        return FloatType::class;
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
            'dms::components.field.number.input',
            [
                FloatType::ATTR_MIN                => 'min',
                FloatType::ATTR_MAX                => 'max',
                FloatType::ATTR_MIN                => 'min',
                FloatType::ATTR_GREATER_THAN       => 'greaterThan',
                FloatType::ATTR_LESS_THAN          => 'lessThan',
                FloatType::ATTR_MAX_DECIMAL_POINTS => 'maxDecimalPlaces',
            ]
        );
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderFieldValue(IField $field, IFieldType $fieldType)
    {
        return $this->renderValueViewWithNullDefault(
            $field,
            'dms::components.field.number.value'
        );
    }
}