<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\InputTransformer;

use Dms\Common\Structure\FileSystem\UploadedFileFactory;
use Dms\Core\Module\IParameterizedAction;
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
     * @param IParameterizedAction $action
     * @param array                $input
     *
     * @return array
     */
    public function transform(IParameterizedAction $action, array $input) : array
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