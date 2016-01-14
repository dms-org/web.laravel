<?php

namespace Dms\Web\Laravel\File\Command;

use Dms\Common\Structure\DateTime\DateTime;
use Dms\Core\Util\IClock;
use Dms\Web\Laravel\File\Persistence\ITemporaryFileRepository;
use Dms\Web\Laravel\File\TemporaryFile;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * The clear temp file command.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ClearTempFilesCommand extends Command
{

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'dms:clear-temp-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the expired temporary files';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ITemporaryFileRepository
     */
    protected $tempFileRepo;

    /**
     * @var IClock
     */
    protected $clock;

    /**
     * ClearTempFilesCommand constructor.
     *
     * @param Filesystem               $filesystem
     * @param ITemporaryFileRepository $tempFileRepo
     * @param IClock                   $clock
     */
    public function __construct(Filesystem $filesystem, ITemporaryFileRepository $tempFileRepo, IClock $clock)
    {
        parent::__construct();
        $this->filesystem   = $filesystem;
        $this->tempFileRepo = $tempFileRepo;
        $this->clock        = $clock;
    }

    public function fire()
    {
        $expiredFiles = $this->tempFileRepo->matching(
                $this->tempFileRepo->criteria()
                        ->whereSatisfies(TemporaryFile::expiredSpec($this->clock))
        );

        foreach ($expiredFiles as $file) {
            if ($file->getFile()->exists()) {
                $this->filesystem->delete($file->getFile()->getFullPath());
                $this->output->writeln("<info>Deleted {$file->getFile()->getFullPath()}</info>");
            }
        }

        $this->tempFileRepo->removeAll($expiredFiles);
    }
}