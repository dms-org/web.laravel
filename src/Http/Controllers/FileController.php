<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers;

use Dms\Common\Structure\FileSystem\UploadedFileFactory;
use Dms\Core\ICms;
use Dms\Core\Model\EntityNotFoundException;
use Dms\Web\Laravel\Error\DmsError;
use Dms\Web\Laravel\File\ITemporaryFileService;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * The file upload/download controller.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileController extends DmsController
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
     * FileController constructor.
     *
     * @param ICms                  $cms
     * @param ITemporaryFileService $tempFileService
     * @param Repository            $config
     */
    public function __construct(ICms $cms, ITemporaryFileService $tempFileService, Repository $config)
    {
        parent::__construct($cms);

        $this->tempFileService = $tempFileService;
        $this->config          = $config;
    }

    public function upload(Request $request)
    {
        $tokens = [];

        /** @var UploadedFile $file */
        foreach ($request->files->all() as $key => $file) {
            $tokens[$key] = $this->tempFileService->storeTempFile(
                UploadedFileFactory::build(
                    $file->getRealPath(),
                    $file->getError(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType()
                ),
                $this->config->get('dms.storage.temp-files.upload-expiry')
            )->getToken();
        }

        return response()->json([
            'message' => 'The files were successfully uploaded',
            'tokens'  => $tokens,
        ]);
    }

    public function preview($token)
    {
        try {
            $file = $this->tempFileService->getTempFile($token);

            $isImage = @getimagesize($file->getFile()->getFullPath()) !== false;

            if ($isImage) {
                return \response()
                    ->download($file->getFile()->getInfo(), $file->getFile()->getClientFileNameWithFallback());
            }

            return \response('', 404);
        } catch (EntityNotFoundException $e) {
            DmsError::abort(404);
        }
    }

    public function download($token, CookieJar $cookieJar)
    {
        try {
            $file = $this->tempFileService->getTempFile($token);

            $cookieJar->queue('file-download-' . $token, true, 60, null, null, false, false);
            return \response()
                ->download($file->getFile()->getFullPath(), $file->getFile()->getClientFileNameWithFallback());
        } catch (EntityNotFoundException $e) {
            DmsError::abort(404);
        }
    }
}