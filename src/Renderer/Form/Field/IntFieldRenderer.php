<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\IntType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

/**
 * The integer field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass() : string
    {
        return IntType::class;
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return bool
     */
    protected function canRender(IField $field, IFieldType $fieldType) : bool
    {
        return !$fieldType->has(FieldType::ATTR_OPTIONS);
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderField(IField $field, IFieldType $fieldType) : string
    {
        return $this->renderView(
            $field,
            'dms::components.field.number.input',
            [
                IntType::ATTR_MIN          => 'min',
                IntType::ATTR_MAX          => 'max',
                IntType::ATTR_MIN          => 'min',
                IntType::ATTR_GREATER_THAN => 'greaterThan',
                IntType::ATTR_LESS_THAN    => 'lessThan',
            ]
        );
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderFieldValue(IField $field, IFieldType $fieldType) : string
    {
        return $this->renderValueViewWithNullDefault(
            $field,
            'dms::components.field.number.value'
        );
    }
}