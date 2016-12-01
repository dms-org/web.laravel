<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold;

use Dms\Core\Model\IEntity;
use Dms\Core\Model\IValueObject;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\ValueObject;
use Illuminate\Filesystem\Filesystem;
use Pinq\Traversable;
use Symfony\Component\Finder\Finder;

/**
 * The domain structure loader
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DomainStructureLoader
{
    /**
     * @var bool
     */
    protected $hasLoadedApplicationClasses = false;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * DomainStructureLoader constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $namespace
     *
     * @return string[]
     */
    public function getAllRootEntitiesUnder(string $namespace) : array
    {
        $entities = $this->getAllEntitiesUnder($namespace);

        return Traversable::from($entities)
            ->where(function (string $entity) {
                return get_parent_class($entity) === Entity::class;
            })
            ->asArray();
    }

    /**
     * @param string $namespace
     *
     * @return string[]
     */
    public function getAllRootValueObjectsUnder(string $namespace) : array
    {
        $valueObjects = $this->getAllValueObjectsUnder($namespace);

        return Traversable::from($valueObjects)
            ->where(function (string $valueObject) {
                return get_parent_class($valueObject) === ValueObject::class;
            })
            ->asArray();
    }

    /**
     * @param string $namespace
     *
     * @return string[]
     */
    public function getAllEntitiesUnder(string $namespace) : array
    {
        $this->loadAllApplicationClasses();

        return Traversable::from(get_declared_classes())
            ->where(function (string $class) {
                return is_subclass_of($class, IEntity::class, true);
            })
            ->where(function (string $class) use ($namespace) {
                return starts_with($class, $namespace);
            })
            ->asArray();
    }

    /**
     * @param string $namespace
     *
     * @return string[]
     */
    public function getAllValueObjectsUnder(string $namespace) : array
    {
        $this->loadAllApplicationClasses();

        return Traversable::from(get_declared_classes())
            ->where(function (string $class) {
                return is_subclass_of($class, IValueObject::class, true);
            })
            ->where(function (string $class) use ($namespace) {
                return starts_with($class, $namespace);
            })
            ->asArray();
    }

    /**
     * @return void
     */
    protected function loadAllApplicationClasses()
    {
        if ($this->hasLoadedApplicationClasses) {
            return;
        }

        foreach (Finder::create()->files()->in(app_path()) as $file) {
            /** @var \SplFileInfo $file */
            if (ends_with($file->getFilename(), '.php')) {
                require_once $file->getRealPath();
            }
        }

        $this->hasLoadedApplicationClasses = true;
    }
}