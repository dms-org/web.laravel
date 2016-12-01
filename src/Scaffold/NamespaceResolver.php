<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold;

use Dms\Common\Structure\FileSystem\PathHelper;
use Illuminate\Console\AppNamespaceDetectorTrait;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NamespaceResolver
{
    use AppNamespaceDetectorTrait {
        getAppNamespace as baseGetAppNamespace;
    }

    /**
     * @param string $directory
     *
     * @return string
     */
    public function getNamespaceFor(string $directory) : string
    {
        return $this->getAppNamespace() . '\\' . trim(str_replace('/', '\\', substr(PathHelper::normalize($directory), strlen(PathHelper::normalize(app_path())))), '\\');
    }

    protected function getAppNamespace() : string
    {
        return trim($this->baseGetAppNamespace(), '\\');
    }
}