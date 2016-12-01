<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold;

use Dms\Common\Structure\FileSystem\PathHelper;
use Dms\Core\Exception\InvalidOperationException;

/**
 * The dms:scaffold:persistence command
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ScaffoldPersistenceCommand extends ScaffoldCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'dms:scaffold:persistence
                            {entity_namespace=App\\Domain\\Entities : The namespace of the entities}
                            {output_dir_abstract=app/Domain/Services/Persistence : The path to place the repository interfaces.}
                            {output_dir_implementation=app/Infrastructure/Persistence : The path to place the repository and mapper implementations.}
                            {--overwrite : Whether to overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffolds the persistence layer for a set of entities';


    /**
     * Execute the console command.
     *
     * @throws InvalidOperationException
     */
    public function fire()
    {
        $namespace = ltrim($this->input->getArgument('entity_namespace'), '\\');

        $abstractDirectory       = $this->input->getArgument('output_dir_abstract');
        $implementationDirectory = $this->input->getArgument('output_dir_implementation');
        $overwrite               = (bool)$this->input->hasOption('--overwrite');
        $entities                = $this->domainStructureLoader->getAllRootEntitiesUnder($namespace);
        $valueObjects            = $this->domainStructureLoader->getAllRootValueObjectsUnder($namespace);

        if (!$valueObjects && !$entities) {
            $this->output->error('No entities found under ' . $namespace . ' namespace');

            return;
        }

        foreach ($entities as $entity) {
            list($repositoryClass, $repositoryShortClassName) = $this->generateRepositoryInterface($abstractDirectory, $namespace, $entity, $overwrite);
            $this->generateEntityMapper($implementationDirectory, $namespace, $entity, $overwrite);
            $this->generateRepositoryImplementation($implementationDirectory, $namespace, $entity, $repositoryClass, $repositoryShortClassName, $overwrite);
        }

        foreach ($valueObjects as $valueObject) {
            $this->generateValueObjectMapper($implementationDirectory, $namespace, $valueObject, $overwrite);
        }

        $this->output->success('Done!');
    }

    private function generateRepositoryInterface(string $abstractDirectory, string $namespace, string $entity, bool $overwrite)
    {
        $entityName        = (new \ReflectionClass($entity))->getShortName();
        $entityNamespace   = (new \ReflectionClass($entity))->getNamespaceName();
        $relativeNamespace = trim(substr($entityNamespace, strlen($namespace)), '\\');

        $repositoryName      = 'I' . $entityName . 'Repository';
        $repositoryNamespace = $this->namespaceResolver->getNamespaceFor($this->getAbsolutePath($abstractDirectory)) . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $repositoryClass     = $repositoryNamespace . '\\' . $repositoryName;
        $repositoryDirectory = PathHelper::combine($abstractDirectory, $relativeNamespace);

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Persistence/RepositoryInterface.php.stub');

        $php = strtr($php, [
            '{namespace}'   => $repositoryNamespace,
            '{name}'        => $repositoryName,
            '{entity}'      => $entity,
            '{entity_name}' => $entityName,
        ]);

        $this->createFile(PathHelper::combine($repositoryDirectory, $repositoryName . '.php'), $php, $overwrite);

        return [$repositoryClass, $repositoryName];
    }

    private function generateEntityMapper(string $implementationDirectory, string $namespace, string $entity, bool $overwrite)
    {
        $entityName        = (new \ReflectionClass($entity))->getShortName();
        $entityNamespace   = (new \ReflectionClass($entity))->getNamespaceName();
        $relativeNamespace = trim(substr($entityNamespace, strlen($namespace)), '\\');

        $mapperName      = $entityName . 'Mapper';
        $mapperNamespace = $this->namespaceResolver->getNamespaceFor($this->getAbsolutePath($implementationDirectory)) . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $mapperDirectory = PathHelper::combine($implementationDirectory, $relativeNamespace);

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Persistence/EntityMapper.php.stub');

        $php = strtr($php, [
            '{namespace}'   => $mapperNamespace,
            '{name}'        => $mapperName,
            '{entity}'      => $entity,
            '{entity_name}' => $entityName,
            '{table_name}'  => str_plural(snake_case($entityName)),
        ]);

        $this->createFile(PathHelper::combine($mapperDirectory, $mapperName . '.php'), $php, $overwrite);
    }

    private function generateRepositoryImplementation(
        string $implementationDirectory,
        string $namespace,
        string $entity,
        string $interfaceClass,
        string $interfaceName,
        bool $overwrite
    ) {
        $entityName        = (new \ReflectionClass($entity))->getShortName();
        $entityNamespace   = (new \ReflectionClass($entity))->getNamespaceName();
        $relativeNamespace = trim(substr($entityNamespace, strlen($namespace)), '\\');

        $repositoryName      = 'Db' . $entityName . 'Repository';
        $repositoryNamespace = $this->namespaceResolver->getNamespaceFor($this->getAbsolutePath($implementationDirectory)) . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $repositoryDirectory = PathHelper::combine($implementationDirectory, $relativeNamespace);

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Persistence/RepositoryImplementation.php.stub');

        $php = strtr($php, [
            '{namespace}'      => $repositoryNamespace,
            '{name}'           => $repositoryName,
            '{entity}'         => $entity,
            '{entity_name}'    => $entityName,
            '{interface}'      => $interfaceClass,
            '{interface_name}' => $interfaceName,
        ]);

        $this->createFile(PathHelper::combine($repositoryDirectory, $repositoryName . '.php'), $php, $overwrite);
    }

    private function generateValueObjectMapper(string $implementationDirectory, string $namespace, string $valueObject, bool $overwrite)
    {
        $valueObjectName      = (new \ReflectionClass($valueObject))->getShortName();
        $valueObjectNamespace = (new \ReflectionClass($valueObject))->getNamespaceName();
        $relativeNamespace    = trim(substr($valueObjectNamespace, strlen($namespace)), '\\');

        $mapperName      = $valueObjectName . 'Mapper';
        $mapperNamespace = $this->namespaceResolver->getNamespaceFor($this->getAbsolutePath($implementationDirectory)) . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $mapperDirectory = PathHelper::combine($implementationDirectory, $relativeNamespace);

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Persistence/ValueObjectMapper.php.stub');

        $php = strtr($php, [
            '{namespace}'         => $mapperNamespace,
            '{name}'              => $mapperName,
            '{value_object}'      => $valueObject,
            '{value_object_name}' => $valueObjectName,
        ]);

        $this->createFile(PathHelper::combine($mapperDirectory, $mapperName . '.php'), $php, $overwrite);
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