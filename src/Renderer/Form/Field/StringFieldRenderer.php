<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\StringType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

/**
 * The string field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StringFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass() : string
    {
        return StringType::class;
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return bool
     */
    protected function canRender(IField $field, IFieldType $fieldType) : bool
    {
        return !$fieldType->has(FieldType::ATTR_OPTIONS)
        && !$fieldType->get(StringType::ATTR_MULTILINE)
        && $fieldType->get(StringType::ATTR_STRING_TYPE) !== StringType::TYPE_HTML;
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderField(IField $field, IFieldType $fieldType) : string
    {
        /** @var StringType $fieldType */
        $inputType = $this->getInputType($fieldType);

        return $this->renderView(
            $field,
            'dms::components.field.string.input',
            [
                StringType::ATTR_EXACT_LENGTH => 'exactLength',
                StringType::ATTR_MIN_LENGTH   => 'minLength',
                StringType::ATTR_MAX_LENGTH   => 'maxLength',
            ],
            ['type' => $inputType]
        );
    }

    private function getInputType(StringType $fieldType)
    {
        switch ($fieldType->get(StringType::ATTR_STRING_TYPE)) {
            case StringType::TYPE_URL:
                return 'url';

            case StringType::TYPE_IP_ADDRESS:
                return 'ip-address';

            case StringType::TYPE_EMAIL:
                return 'email';

            case StringType::TYPE_PASSWORD:
                return 'password';

            default:
                return 'text';
        }
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderFieldValue(IField $field, IFieldType $fieldType) : string
    {
        /** @var StringType $fieldType */
        $inputType = $this->getInputType($fieldType);

        return $this->renderValueViewWithNullDefault(
            $field,
            'dms::components.field.string.value',
            [
                'type' => $inputType,
            ]
        );
    }
}