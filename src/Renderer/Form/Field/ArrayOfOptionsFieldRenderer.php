<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

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

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return bool
     */
    protected function canRender(IField $field, IFieldType $fieldType) : bool
    {
        /** @var ArrayOfType $fieldType */
        return !$fieldType->has(FieldType::ATTR_OPTIONS)
        && $fieldType->getElementType()->has(FieldType::ATTR_OPTIONS)
        && $fieldType->get(ArrayOfType::ATTR_UNIQUE_ELEMENTS);
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderField(IField $field, IFieldType $fieldType) : string
    {
        /** @var ArrayOfType $fieldType */
        $elementField = $this->makeElementField($fieldType);

        return $this->renderView(
            $field,
            'dms::components.field.checkbox-group.input',
            [
                ArrayOfType::ATTR_MIN_ELEMENTS   => 'minElements',
                ArrayOfType::ATTR_MAX_ELEMENTS   => 'maxElements',
                ArrayOfType::ATTR_EXACT_ELEMENTS => 'exactElements',
            ],
            [
                'options'       => $elementField->getType()->get(ArrayOfType::ATTR_OPTIONS)->getAll(),
                'fieldRenderer' => $this->fieldRendererCollection->findRendererFor($elementField)->render($elementField),
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
        /** @var ArrayOfType $fieldType */
        $elementField = $this->makeElementField($fieldType);

        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.checkbox-group.value',
            [
                'options' => $elementField->getType()->get(ArrayOfType::ATTR_OPTIONS)->getAll(),
            ]
        );
    }

    protected function makeElementField(ArrayOfType $fieldType)
    {
        return Field::element()
            ->type($fieldType->getElementType())
            ->attrs($fieldType->getAll([FieldType::ATTR_READ_ONLY]))
            ->attr(FieldType::ATTR_REQUIRED, false)
            ->build();
    }
}