<?php

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Common\Structure\DateTime\Form\DateOrTimeRangeType;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Renderer\Form\FormRenderer;

/**
 * The inner-form field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerFormFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass()
    {
        return InnerFormType::class;
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return bool
     */
    protected function canRender(IField $field, IFieldType $fieldType)
    {
        return !$fieldType->has(FieldType::ATTR_OPTIONS)
        && !($fieldType instanceof DateOrTimeRangeType);
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderField(IField $field, IFieldType $fieldType)
    {
        /** @var InnerFormType $fieldType */
        $formWithArrayFields = $fieldType->getInnerArrayForm($field->getName());
        $formRenderer        = new FormRenderer($this->fieldRendererCollection);


        return $this->renderView(
                $field,
                'dms::components.form.field.inner-form.input',
                [],
                [
                        'formContent' => $formRenderer->renderFields($formWithArrayFields)
                ]
        );
    }
}