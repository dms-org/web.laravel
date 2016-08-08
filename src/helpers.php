<?php

if (!function_exists('asset_file')) {
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