<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold;

use Dms\Common\Structure\FileSystem\PathHelper;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\ValueObject;
use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Pinq\Traversable;
use Symfony\Component\Finder\Finder;

/**
 * The dms:scaffold command base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ScaffoldCommand extends Command
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DomainStructureLoader
     */
    protected $domainStructureLoader;

    /**
     * @var NamespaceResolver
     */
    protected $namespaceResolver;

    /**
     * ScaffoldCommand constructor.
     *
     * @param Filesystem            $filesystem
     * @param DomainStructureLoader $domainStructureLoader
     */
    public function __construct(Filesystem $filesystem, DomainStructureLoader $domainStructureLoader, NamespaceResolver $namespaceResolver)
    {
        parent::__construct();

        $this->filesystem            = $filesystem;
        $this->domainStructureLoader = $domainStructureLoader;
        $this->namespaceResolver = $namespaceResolver;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function getAbsolutePath(string $path)
    {
        if (str_contains($path, ':') || starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    protected function createFile(string $filePath, string $code, bool $overwrite)
    {
        $this->filesystem->makeDirectory(dirname($filePath), 0755, true, true);

        if (!$overwrite && $this->filesystem->exists($filePath)) {
            return;
        }

        $this->filesystem->put($filePath, $code);
    }
}