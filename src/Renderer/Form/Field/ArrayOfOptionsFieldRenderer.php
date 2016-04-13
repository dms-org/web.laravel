<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldOptions;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Renderer\Form\FormRenderingContext;

/**
 * The array of options field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfOptionsFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return array
     */
    public function getFieldTypeClasses() : array
    {
        return [ArrayOfType::class];
    }

    protected function canRender(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType) : bool
    {
        /** @var ArrayOfType $fieldType */
        return !$fieldType->has(FieldType::ATTR_OPTIONS)
        && $fieldType->getElementType()->has(FieldType::ATTR_OPTIONS)
        && $fieldType->get(ArrayOfType::ATTR_UNIQUE_ELEMENTS);
    }

    protected function renderField(
        FormRenderingContext $renderingContext,
        IField $field,
        IFieldType $fieldType
    ) : string
    {
        /** @var ArrayOfType $fieldType */
        $elementField = $this->makeElementField($fieldType);

        $options = $elementField->getType()->get(ArrayOfType::ATTR_OPTIONS);

        return $this->renderView(
            $field,
            'dms::components.field.checkbox-group.input',
            [
                ArrayOfType::ATTR_MIN_ELEMENTS   => 'minElements',
                ArrayOfType::ATTR_MAX_ELEMENTS   => 'maxElements',
                ArrayOfType::ATTR_EXACT_ELEMENTS => 'exactElements',
            ],
            [
                'options' => $this->getOptionsWithValuesAsKeys($options),
            ]
        );
    }

    protected function renderFieldValue(
        FormRenderingContext $renderingContext,
        IField $field,
        $value,
        IFieldType $fieldType
    ) : string
    {
        /** @var ArrayOfType $fieldType */
        $elementField = $this->makeElementField($fieldType);

        $options     = $elementField->getType()->get(ArrayOfType::ATTR_OPTIONS);
        $urlCallback = RelatedEntityLinker::getUrlCallbackFor($options);

        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.checkbox-group.value',
            [
                'options'     => $this->getOptionsWithValuesAsKeys($options),
                'urlCallback' => $urlCallback,
            ]
        );
    }

    protected function makeElementField(ArrayOfType $fieldType)
    {
        return $fieldType->getElementField()
            ->withTypeAttributes([FieldType::ATTR_REQUIRED => false]);
    }

    private function getOptionsWithValuesAsKeys(IFieldOptions $options) : array
    {
        $indexedOptions = [];
        
        foreach($options->getAll() as $fieldOption) {
            $indexedOptions[$fieldOption->getValue()] = $fieldOption;
        }
        
        return $indexedOptions;
    }
}