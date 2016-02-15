<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Action\InputTransformer;

use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\IActionInputTransformer;
use Dms\Web\Laravel\File\ITemporaryFileService;

/**
 * Transforms any temp uploaded files referenced by token to the equivalent uploaded file input.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TempUploadedFileToUploadedFileTransformer implements IActionInputTransformer
{
    const TEMP_FILES_KEY = '__temp_uploaded_files';

    /**
     * @var ITemporaryFileService
     */
    protected $tempFileService;

    /**
     * TempUploadedFileToUploadedFileTransformer constructor.
     *
     * @param ITemporaryFileService $tempFileService
     */
    public function __construct(ITemporaryFileService $tempFileService)
    {
        $this->tempFileService = $tempFileService;
    }

    /**
     * Transforms for the supplied action.
     *
     * @param IAction $action
     * @param array   $input
     *
     * @return array
     */
    public function transform(IAction $action, array $input) : array
    {
        if (isset($input[self::TEMP_FILES_KEY]) && is_array($input[self::TEMP_FILES_KEY])) {
            $uploadedTokenStructure = $input[self::TEMP_FILES_KEY];
            $uploadedFileTokens = [];

            array_walk_recursive($uploadedTokenStructure, function ($token) use (&$uploadedFileTokens) {
                $uploadedFileTokens[] = $token;
            });

            $uploadedFiles = [];
            foreach ($this->tempFileService->getTempFiles($uploadedFileTokens) as $file) {
                $uploadedFiles[$file->getToken()] = $file->getFile();
            }

            $uploadedFileStructure = $uploadedTokenStructure;
            array_walk_recursive($uploadedFileStructure, function (&$token) use (&$uploadedFiles) {
                $token = $uploadedFiles[$token];
            });

            unset($input[self::TEMP_FILES_KEY]);
        }

        return array_replace_recursive($input, $uploadedFileStructure);
    }
}