<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Common\Structure\FileSystem\Form\FileUploadType;
use Dms\Common\Structure\FileSystem\Form\ImageUploadType;
use Dms\Core\File\IFile;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\File\ITemporaryFileService;
use Illuminate\Contracts\Config\Repository;

/**
 * The file field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileFieldRenderer extends BladeFieldRenderer
{
    /**
     * @var ITemporaryFileService
     */
    protected $tempFileService;

    /**
     * @var Repository
     */
    private $config;

    public function __construct(ITemporaryFileService $tempFileService, Repository $config)
    {
        parent::__construct();
        $this->tempFileService = $tempFileService;
        $this->config          = $config;
    }


    /**
     * Gets the expected class of the field type for the field.
     *
     * @return array
     */
    public function getFieldTypeClasses() : array
    {
        return [FileUploadType::class];
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
                FileUploadType::ATTR_MAX_SIZE    => 'maxFileSize',
                FileUploadType::ATTR_MIN_SIZE    => 'minFileSize',
                FileUploadType::ATTR_EXTENSIONS  => 'extensions',
                ImageUploadType::ATTR_MAX_WIDTH  => 'maxImageWidth',
                ImageUploadType::ATTR_MAX_HEIGHT => 'maxImageHeight',
                ImageUploadType::ATTR_MIN_WIDTH  => 'minImageWidth',
                ImageUploadType::ATTR_MIN_HEIGHT => 'minImageHeight',
            ],
            [
                'imagesOnly'    => $fieldType instanceof ImageUploadType,
                'existingFiles' => $this->getExistingFilesArray([$field->getUnprocessedInitialValue()]),
            ]
        );
    }

    protected function getExistingFilesArray(array $files) : array
    {
        /** @var IFile[] $existingFiles */
        $existingFiles = [];

        foreach ($files as $file) {
            if (empty($file['file'])) {
                continue;
            }

            /** @var IFile $file */
            $existingFiles[] = $file['file'];
        }

        $tempFiles = $this->tempFileService->storeTempFiles(
            $existingFiles,
            $this->config->get('dms.storage.temp-files.download-expiry')
        );

        $data = [];

        foreach ($existingFiles as $key => $file) {
            $tempFile        = $tempFiles[$key];
            $imageDimensions = @getimagesize($file->getFullPath());

            $data[] = [
                    'name'        => $file->getClientFileNameWithFallback(),
                    'size'        => $file->exists() ? $file->getSize() : 0,
                    'previewUrl'  => route('dms::file.preview', $tempFile->getToken()),
                    'downloadUrl' => route('dms::file.download', $tempFile->getToken()),
                ] + ($imageDimensions ? ['width' => $imageDimensions[0], 'height' => $imageDimensions[1]] : []);
        }

        return $data;
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
            'dms::components.field.dropzone.value',
            [
                'existingFiles' => $value !== null
                    ? $this->getExistingFilesArray([$field->getUnprocessedInitialValue()])
                    : null,
            ]
        );
    }
}