<?php

namespace Dms\Web\Laravel\Action\InputTransformer;

use Dms\Common\Structure\FileSystem\UploadedFileFactory;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\IActionInputTransformer;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

/**
 * Converts symphony uploaded files to the equivalent dms uploaded file class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SymphonyToDmsUploadedFileTransformer implements IActionInputTransformer
{
    /**
     * Transforms for the supplied action.
     *
     * @param IAction $action
     * @param array   $input
     *
     * @return array
     */
    public function transform(IAction $action, array $input)
    {
        array_walk_recursive($input, function (&$value) {
            if ($value instanceof SymfonyUploadedFile) {
                $value = UploadedFileFactory::build(
                    $value->getRealPath(),
                    $value->getError(),
                    $value->getClientOriginalName(),
                    $value->getClientMimeType()
                );
            }
        });

        return $input;
    }
}