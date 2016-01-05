<?php

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

/**
 * The radio-group options field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RadioOptionsFieldRender extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass()
    {
        return FieldType::class;
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return bool
     */
    protected function canRender(IField $field, IFieldType $fieldType)
    {
        return $fieldType->has(FieldType::ATTR_OPTIONS)
        && $fieldType->get(FieldType::ATTR_SHOW_ALL_OPTIONS);
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
                'dms::components.form.field.radio-group.input',
                [
                        FieldType::ATTR_OPTIONS => 'options',
                ]
        );
    }
}