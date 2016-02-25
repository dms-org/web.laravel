<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Common\Structure\FileSystem\Form\FileUploadType;
use Dms\Core\File\IFile;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Renderer\Form\FormRenderer;

/**
 * The file field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass() : string
    {
        return FileUploadType::class;
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
            'dms::components.field.dropzone.input',
            [
                'maxFileSize' => FileUploadType::ATTR_MAX_SIZE,
                'minFileSize' => FileUploadType::ATTR_MIN_SIZE,
                'extensions'  => FileUploadType::ATTR_EXTENSIONS,
            ],
            [

                'existingFile' => $this->getExistingFile($field->getUnprocessedInitialValue())
            ]
        );
    }

    private function getExistingFile(array $initialValue = null)
    {
        if (empty($initialValue['file'])) {
            return null;
        }

        /** @var IFile $file */
        $file = $initialValue['file'];
        return [
            'name' => $file->getClientFileNameWithFallback(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderFieldValue(IField $field, $value, IFieldType $fieldType) : string
    {
        /** @var InnerFormType $fieldType */
        $formWithArrayFields = $fieldType->getInnerArrayForm($field->getName());
        $formRenderer        = new FormRenderer($this->fieldRendererCollection);

        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.dropzone.value',
            [

            ]
        );
    }
}