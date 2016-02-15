<?php declare(strict_types=1);

namespace Dms\Web\Laravel\File;

use Dms\Core\File\IFile;
use Dms\Core\Model\EntityNotFoundException;

/**
 * The temporary file service interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITemporaryFileService
{
    /**
     * Stores the supplied file as a temporary file.
     *
     * @param IFile $file
     * @param int   $expirySeconds The amount of seconds from now for the file to expire
     *
     * @return TemporaryFile
     */
    public function storeTempFile(IFile $file, int $expirySeconds) : TemporaryFile;

    /**
     * Gets the temp file from the supplied token
     *
     * @param string $token
     *
     * @return TemporaryFile
     * @throws EntityNotFoundException
     */
    public function getTempFile(string $token) : TemporaryFile;

    /**
     * Gets the temp files from the supplied tokens
     *
     * @param string[] $tokens
     *
     * @return TemporaryFile[]
     * @throws EntityNotFoundException
     */
    public function getTempFiles(array $tokens) : array;
}