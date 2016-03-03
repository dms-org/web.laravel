<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Renderer\Form\FormRenderingContext;

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
     * @return array
     */
    public function getFieldTypeClasses() : array
    {
        return [FieldType::class];
    }

    protected function canRender(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType) : bool
    {
        return $fieldType instanceof ArrayOfType
            && $fieldType->getElementType() instanceof InnerFormType
            && $fieldType->getElementType();
    }

    protected function renderField(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType) : string
    {
        return $this->renderView(
            $field,
            'dms::components.field.radio-group.input',
            [
                FieldType::ATTR_OPTIONS => 'options',
            ]
        );
    }

    protected function renderFieldValue(FormRenderingContext $renderingContext, IField $field, $value, IFieldType $fieldType) : string
    {
        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.string.value'
        );
    }
}