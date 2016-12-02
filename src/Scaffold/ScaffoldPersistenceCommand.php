<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold;

use Dms\Common\Structure\FileSystem\PathHelper;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\Object\Entity;
use Dms\Web\Laravel\Scaffold\CodeGeneration\PhpCodeBuilderContext;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;

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
                            {output_abstract_namespace=App\\Domain\\Services\\Persistence : The path to place the repository interfaces.}
                            {output_implementation_namespace=App\\Infrastructure\\Persistence : The path to place the repository and mapper implementations.}
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

        $abstractNamespace       = $this->input->getArgument('output_abstract_namespace');
        $implementationNamespace = $this->input->getArgument('output_implementation_namespace');
        $overwrite               = (bool)$this->input->hasOption('--overwrite');
        $domain                  = $this->domainStructureLoader->loadDomainStructure($namespace);
        $entities                = $domain->getRootEntities();
        $valueObjects            = $domain->getRootValueObjects();

        if (!$valueObjects && !$entities) {
            $this->output->error('No entities found under ' . $namespace . ' namespace');

            return;
        }

        foreach ($entities as $entity) {
            list($repositoryClass, $repositoryShortClassName) = $this->generateRepositoryInterface($abstractNamespace, $namespace, $entity, $overwrite);
            $this->generateEntityMapper($implementationNamespace, $namespace, $entity, $overwrite);
            $this->generateRepositoryImplementation($implementationNamespace, $namespace, $entity, $repositoryClass, $repositoryShortClassName, $overwrite);
        }

        foreach ($valueObjects as $valueObject) {
            $this->generateValueObjectMapper($implementationNamespace, $namespace, $valueObject, $overwrite);
        }

        $this->output->success('Done!');
    }

    private function generateRepositoryInterface(string $abstractNamespace, string $namespace, DomainObjectStructure $entity, bool $overwrite)
    {
        $entityName        = $entity->getReflection()->getShortName();
        $entityNamespace   = $entity->getReflection()->getNamespaceName();
        $relativeNamespace = trim(substr($entityNamespace, strlen($namespace)), '\\');

        $repositoryName      = 'I' . $entityName . 'Repository';
        $repositoryNamespace = $abstractNamespace . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $repositoryDirectory = $this->namespaceResolver->getDirectoryFor($abstractNamespace);
        $repositoryClass     = $repositoryNamespace . '\\' . $repositoryName;

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Persistence/RepositoryInterface.php.stub');

        $php = strtr($php, [
            '{namespace}'   => $repositoryNamespace,
            '{name}'        => $repositoryName,
            '{entity}'      => $entity->getDefinition()->getClassName(),
            '{entity_name}' => $entityName,
        ]);

        $this->createFile(PathHelper::combine($repositoryDirectory, $repositoryName . '.php'), $php, $overwrite);

        return [$repositoryClass, $repositoryName];
    }

    private function generateEntityMapper(string $implementationNamespace, string $namespace, DomainObjectStructure $entity, bool $overwrite)
    {
        $entityName        = $entity->getReflection()->getShortName();
        $entityNamespace   = $entity->getReflection()->getNamespaceName();
        $relativeNamespace = trim(substr($entityNamespace, strlen($namespace)), '\\');

        $mapperName      = $entityName . 'Mapper';
        $mapperNamespace = $implementationNamespace . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $mapperDirectory = $this->namespaceResolver->getDirectoryFor($mapperNamespace);

        $mappingCodeContext = $this->generatePropertyBindingCode($entity, 2);

        $php = $this->buildCodeFile(
            __DIR__ . '/Stubs/Persistence/EntityMapper.php.stub',
            $mappingCodeContext,
            [
                '{namespace}'   => $mapperNamespace,
                '{name}'        => $mapperName,
                '{entity}'      => $entity->getDefinition()->getClassName(),
                '{entity_name}' => $entityName,
                '{table_name}'  => str_plural(snake_case($entityName)),
                '{mapping}'     => $mappingCodeContext->getCode()->getCode(),
            ]
        );

        $this->createFile(PathHelper::combine($mapperDirectory, $mapperName . '.php'), $php, $overwrite);
    }

    protected function generatePropertyBindingCode(DomainObjectStructure $object, int $indent) : PhpCodeBuilderContext
    {
        $code = new PhpCodeBuilderContext();

        $code->getCode()->indent = $indent;

        foreach ($object->getDefinition()->getProperties() as $property) {
            if ($property->getName() === Entity::ID) {
                continue;
            }

            $this->getCodeGeneratorFor($object, $property->getName())->generatePersistenceMappingCode(
                $code,
                $object,
                $property->getName()
            );

            $code->getCode()->appendLine(';');
            $code->getCode()->appendLine('');
        }

        return $code;
    }

    private function generateRepositoryImplementation(
        string $implementationNamespace,
        string $namespace,
        DomainObjectStructure $entity,
        string $interfaceClass,
        string $interfaceName,
        bool $overwrite
    ) {
        $entityName        = $entity->getReflection()->getShortName();
        $entityNamespace   = $entity->getReflection()->getNamespaceName();
        $relativeNamespace = trim(substr($entityNamespace, strlen($namespace)), '\\');

        $repositoryName      = 'Db' . $entityName . 'Repository';
        $repositoryNamespace = $implementationNamespace . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $repositoryDirectory = $this->namespaceResolver->getDirectoryFor($repositoryNamespace);

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Persistence/RepositoryImplementation.php.stub');

        $php = strtr($php, [
            '{namespace}'      => $repositoryNamespace,
            '{name}'           => $repositoryName,
            '{entity}'         => $entity->getDefinition()->getClassName(),
            '{entity_name}'    => $entityName,
            '{interface}'      => $interfaceClass,
            '{interface_name}' => $interfaceName,
        ]);

        $this->createFile(PathHelper::combine($repositoryDirectory, $repositoryName . '.php'), $php, $overwrite);
    }

    private function generateValueObjectMapper(string $implementationNamespace, string $namespace, DomainObjectStructure $valueObject, bool $overwrite)
    {
        $valueObjectName      = $valueObject->getReflection()->getShortName();
        $valueObjectNamespace = $valueObject->getReflection()->getNamespaceName();
        $relativeNamespace    = trim(substr($valueObjectNamespace, strlen($namespace)), '\\');

        $mapperName      = $valueObjectName . 'Mapper';
        $mapperNamespace = $implementationNamespace . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $mapperDirectory = $this->namespaceResolver->getDirectoryFor($mapperNamespace);

        $mappingCodeContext = $this->generatePropertyBindingCode($valueObject, 2);

        $php = $this->buildCodeFile(
            __DIR__ . '/Stubs/Persistence/ValueObjectMapper.php.stub',
            $mappingCodeContext,
            [
                '{namespace}'         => $mapperNamespace,
                '{name}'              => $mapperName,
                '{value_object}'      => $valueObject->getDefinition()->getClassName(),
                '{value_object_name}' => $valueObjectName,
                '{mapping}'           => $mappingCodeContext->getCode()->getCode(),
            ]
        );

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