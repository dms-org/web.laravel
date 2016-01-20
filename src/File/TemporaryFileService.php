<?php

namespace Dms\Web\Laravel\File;

use Dms\Common\Structure\DateTime\DateTime;
use Dms\Core\File\IFile;
use Dms\Core\File\IUploadedFile;
use Dms\Core\Model\EntityNotFoundException;
use Dms\Core\Util\IClock;
use Dms\Web\Laravel\File\Persistence\ITemporaryFileRepository;
use Illuminate\Config\Repository;

/**
 * The temporary file service class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TemporaryFileService implements ITemporaryFileService
{
    /**
     * @var ITemporaryFileRepository
     */
    protected $repo;

    /**
     * @var IClock
     */
    protected $clock;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * TemporaryFileService constructor.
     *
     * @param ITemporaryFileRepository $repo
     * @param IClock                   $clock
     * @param Repository               $config
     */
    public function __construct(ITemporaryFileRepository $repo, IClock $clock, Repository $config)
    {
        $this->repo = $repo;
        $this->clock = $clock;
        $this->config = $config;
    }

    /**
     * Stores the supplied file as a temporary file.
     *
     * @param IFile $file
     * @param int   $expirySeconds The amount of seconds from now for the file to expire
     *
     * @return TemporaryFile
     */
    public function storeTempFile(IFile $file, $expirySeconds)
    {
        $tempUploadDirectory = $this->config->get('dms.storage.temp-files.dir');

        if ($file instanceof IUploadedFile) {
            $file = $file->moveTo($tempUploadDirectory);
        }

        $tempFile = new TemporaryFile(
            str_random(40),
            $file,
            (new DateTime($this->clock->utcNow()))->addSeconds($expirySeconds)
        );

        $this->repo->save($tempFile);
    }

    /**
     * Gets the supplied temp file from the token
     *
     * @param string $token
     *
     * @return TemporaryFile
     * @throws EntityNotFoundException
     */
    public function getTempFile($token)
    {
        return $this->getTempFiles([$token])[0];
    }

    /**
     * Gets the temp files from the supplied tokens
     *
     * @param string[] $tokens
     *
     * @return TemporaryFile[]
     * @throws EntityNotFoundException
     */
    public function getTempFiles(array $tokens)
    {
        $files = $this->repo->matching(
            $this->repo->criteria()
                ->whereIn(TemporaryFile::TOKEN, $tokens)
                ->whereSatisfies(TemporaryFile::notExpiredSpec($this->clock))
        );

        if (count($files) !== count($tokens)) {
            throw new EntityNotFoundException(TemporaryFile::class, implode(',', $tokens), TemporaryFile::TOKEN);
        }

        return $files;
    }
}