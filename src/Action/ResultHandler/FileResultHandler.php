<?php

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\File\IFile;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\File\ITemporaryFileService;
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
    protected function canHandleResult(IAction $action, $result)
    {
        return true;
    }

    /**
     * @param IAction $action
     * @param mixed   $result
     *
     * @return Response|mixed
     */
    protected function handleResult(IAction $action, $result)
    {
        /** @var IFile $result */
        $tempFile = $this->tempFileService->storeTempFile($result, $this->config->get('dms.storage.temp-files.download-expiry'));

        return \response()->json([
                'message' => 'The action was successfully executed',
                'file'    => [
                        'name'  => $tempFile->getFile()->getClientFileNameWithFallback(),
                        'token' => $tempFile->getToken(),
                ]
        ]);
    }
}