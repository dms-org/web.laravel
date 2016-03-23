<?php

namespace Dms\Web\Laravel\Tests\Unit\Action\ResultHandler;

use Dms\Common\Structure\DateTime\DateTime;
use Dms\Common\Structure\FileSystem\File;
use Dms\Web\Laravel\Action\IActionResultHandler;
use Dms\Web\Laravel\Action\ResultHandler\FileResultHandler;
use Dms\Web\Laravel\File\ITemporaryFileService;
use Dms\Web\Laravel\File\TemporaryFile;
use Illuminate\Http\JsonResponse;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileResultHandlerTest extends ResultHandlerTest
{

    protected function buildHandler() : IActionResultHandler
    {
        $tempFileService = $this->getMockForAbstractClass(ITemporaryFileService::class);

        $tempFileService
            ->method('storeTempFile')
            ->willReturn(new TemporaryFile('some-token', $this->mockFile(), new DateTime(new \DateTime())));

        app('config')->set('dms.storage.temp-files.download-expiry', 10);

        return new FileResultHandler($tempFileService, app('config'));
    }

    /**
     * @return File
     */
    protected function mockFile()
    {
        return new File(__FILE__, 'file-name');
    }

    public function resultHandlingTests() : array
    {
        return [
            [
                $this->mockAction(),
                $this->mockFile(),
                new JsonResponse([
                    'message' => trans('dms::action.generic-response'),
                    'files'   => [
                        [
                            'name'  => 'file-name',
                            'token' => 'some-token',
                        ],
                    ],
                ]),
            ],
        ];
    }

    public function unhandleableResultTests() : array
    {
        return [
            [$this->mockAction(\stdClass::class), new \stdClass()],
        ];
    }
}