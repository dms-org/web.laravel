<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Common\Structure\FileSystem\Form\FileUploadType;
use Dms\Common\Structure\FileSystem\Form\ImageUploadType;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

/**
 * The array of files field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfFilesFieldsRenderer extends FileFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass() : string
    {
        return ArrayOfType::class;
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
        && $fieldType->getElementType() instanceof FileUploadType;
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
        /** @var FileUploadType $elementType */
        $elementType = $fieldType->getElementType();

        return $this->renderView(
            $field,
            'dms::components.field.dropzone.input',
            [
                ArrayOfType::ATTR_MIN_ELEMENTS   => 'minFiles',
                ArrayOfType::ATTR_MAX_ELEMENTS   => 'maxFiles',
                ArrayOfType::ATTR_EXACT_ELEMENTS => 'exactFiles',
            ],
            [
                'multiUpload'   => true,
                'maxFileSize'   => $elementType->get(FileUploadType::ATTR_MAX_SIZE),
                'minFileSize'   => $elementType->get(FileUploadType::ATTR_MIN_SIZE),
                'extensions'    => $elementType->get(FileUploadType::ATTR_EXTENSIONS),
                'existingFiles' => $this->getExistingFilesArray($field->getUnprocessedInitialValue() ?? []),

                'imagesOnly'     => $elementType instanceof ImageUploadType,
                'maxImageWidth'  => $elementType->get(ImageUploadType::ATTR_MAX_WIDTH),
                'maxImageHeight' => $elementType->get(ImageUploadType::ATTR_MAX_HEIGHT),
                'minImageWidth'  => $elementType->get(ImageUploadType::ATTR_MIN_WIDTH),
                'minImageHeight' => $elementType->get(ImageUploadType::ATTR_MIN_HEIGHT),
            ]
        );
    }

    /**
     * Renders the supplied field value display as a html string.
     *
     * @param IField     $field
     * @param mixed      $value
     * @param IFieldType $fieldType
     *
     * @return string
     * @throws \Dms\Web\Laravel\Renderer\Form\UnrenderableFieldException
     */
    public function renderFieldValue(IField $field, $value, IFieldType $fieldType) : string
    {
        /** @var ArrayOfType $fieldType */
        $elementField = $this->makeElementField($fieldType);

        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.list-of-fields.value',
            [
                'elementField'  => $elementField,
                'fieldRenderer' => $this->fieldRendererCollection->findRendererFor($elementField),
            ]
        );
    }

    protected function makeElementField(ArrayOfType $fieldType)
    {
        return Field::element()
            ->type($fieldType->getElementType())
            ->attrs($fieldType->getAll([FieldType::ATTR_READ_ONLY]))
            ->build();
    }
}