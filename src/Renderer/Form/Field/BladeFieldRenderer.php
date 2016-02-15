<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;

/**
 * The blade field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class BladeFieldRenderer extends FieldRenderer
{
    /**
     * @var array
     */
    protected $defaultAttributeMap = [
        FieldType::ATTR_INITIAL_VALUE => 'value',
        FieldType::ATTR_READ_ONLY     => 'readonly',
        FieldType::ATTR_REQUIRED      => 'required',
        FieldType::ATTR_DEFAULT       => 'defaultValue',
    ];

    /**
     * @param IField $field
     * @param string $viewName
     * @param array  $attributeVariableMap
     * @param array  $extraParams
     *
     * @return string
     */
    protected function renderView(IField $field, string $viewName, array $attributeVariableMap = [], array $extraParams = []) : string
    {
        $attributeVariableMap += $this->defaultAttributeMap;
        $fieldType = $field->getType();

        $viewParams = [];

        foreach ($attributeVariableMap as $attribute => $variableName) {
            $viewParams[$variableName] = $fieldType->get($attribute);
        }

        return (string)view($viewName)
            ->with('field', $field)
            ->with('name', $field->getName())
            ->with('label', $field->getLabel())
            ->with('fieldType', $fieldType)
            ->with($viewParams)
            ->with($extraParams);
    }


    /**
     * @param IField $field
     * @param string $viewName
     * @param array  $extraParams
     * @param null   $overrideValue
     *
     * @return string
     */
    protected function renderValueViewWithNullDefault(
        IField $field,
        string $viewName,
        array $extraParams = [],
        $overrideValue = null
    ) : string {
        $value = $overrideValue === null ? $field->getInitialValue() : $overrideValue;

        if ($value === null) {
            return (string)view('dms::components.field.null.value');
        }

        return (string)view($viewName)
            ->with('value', $value)
            ->with('name', $field->getName())
            ->with('label', $field->getLabel())
            ->with('value', $value)
            ->with($extraParams);
    }
}