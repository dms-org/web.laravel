<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

/**
 * The select-box options field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SelectOptionsFieldRender extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass() : string
    {
        return FieldType::class;
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return bool
     */
    protected function canRender(IField $field, IFieldType $fieldType) : bool
    {
        return $fieldType->has(FieldType::ATTR_OPTIONS)
        && !$fieldType->get(FieldType::ATTR_SHOW_ALL_OPTIONS);
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
            'dms::components.field.select.input',
            [
                FieldType::ATTR_OPTIONS => 'options',
            ]
        );
    }

    /**
     * @param IField     $field
     * @param mixed      $value
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderFieldValue(IField $field, $value, IFieldType $fieldType) : string
    {
        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.string.value'
        );
    }
}