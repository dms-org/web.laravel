<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\File\IFile;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\File\ITemporaryFileService;
use Dms\Web\Laravel\Http\ModuleContext;
use Illuminate\Config\Repository;
use Illuminate\Http\Response;

/**
 * The file action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileResultHandler extends ActionResultHandler
{
    /**
     * @var ITemporaryFileService
     */
    protected $tempFileService;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * FileResultHandler constructor.
     *
     * @param ITemporaryFileService $tempFileService
     * @param Repository            $config
     */
    public function __construct(ITemporaryFileService $tempFileService, Repository $config)
    {
        parent::__construct();
        $this->tempFileService = $tempFileService;
        $this->config          = $config;
    }


    /**
     * @return string|null
     */
    protected function supportedResultType()
    {
        return IFile::class;
    }

    /**
     * @param IAction $action
     * @param mixed   $result
     *
     * @return bool
     */
    protected function canHandleResult(ModuleContext $moduleContext, IAction $action, $result) : bool
    {
        return true;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param mixed         $result
     *
     * @return Response|mixed
     */
    protected function handleResult(ModuleContext $moduleContext, IAction $action, $result)
    {
        /** @var IFile $result */
        $tempFile = $this->tempFileService->storeTempFile(
            $result,
            $this->config->get('dms.storage.temp-files.download-expiry')
        );

        return \response()->json([
            'message' => 'The action was successfully executed',
            'files'   => [
                [
                    'name'  => $tempFile->getFile()->getClientFileNameWithFallback(),
                    'token' => $tempFile->getToken(),
                ],
            ],
        ]);
    }
}