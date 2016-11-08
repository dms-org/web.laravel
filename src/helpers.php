<?php

if (!function_exists('asset_file_url')) {
    /**
     * Generates an asset URL from a file stored within the public directory
     *
     * @param \Dms\Core\File\IFile $file
     *
     * @return string
     * @throws \Dms\Core\Exception\InvalidArgumentException
     */
    function asset_file_url(\Dms\Core\File\IFile $file) : string
    {
        $s3Prefixes = ['s3://', 's3-dms://'];

        foreach($s3Prefixes as $s3Prefix) {
            $isFileOnS3 = starts_with($file->getFullPath(), $s3Prefix);

            if ($isFileOnS3) {
                list($bucketName, $objectKey) = explode('/', substr($file->getFullPath(), strlen($s3Prefix)), 2);

                return 'https://' . $bucketName . '.s3.amazonaws.com/' . $objectKey;
            }
        }

        $filePath   = $file->exists() ? realpath($file->getFullPath()) : $file->getFullPath();
        $publicPath = realpath(public_path());

        if (!starts_with($filePath, $publicPath)) {
            throw \Dms\Core\Exception\InvalidArgumentException::format(
                'Invalid call to %s: the supplied file must be located within the application public directory \'%s\', \'%s\' given',
                __FUNCTION__, $publicPath, $filePath
            );
        }

        $relativePath = substr($filePath, strlen($publicPath));

        return asset(str_replace(DIRECTORY_SEPARATOR, '/', $relativePath));
    }
}