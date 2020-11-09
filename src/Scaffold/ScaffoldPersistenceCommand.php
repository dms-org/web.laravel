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
                            {--overwrite : Whether to overwrite existing files}
                            {--filter= : A filter pattern to restrict which entities are scaffolded e.g App\\Domain\\Entities\\Specific\\*}';

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
    public function handle()
    {
        $domain       = $this->domainStructureLoader->loadDomainStructure($this->input->getArgument('entity_namespace'));

        $context = new ScaffoldPersistenceContext(
            $this->input->getArgument('entity_namespace'),
            $domain,
            $this->input->getArgument('output_abstract_namespace'),
            $this->input->getArgument('output_implementation_namespace')
        );

        $overwrite    = $this->input->hasOption('overwrite') && (bool)$this->input->getOption('overwrite');
        $entities     = $domain->getRootEntities();
        $valueObjects = $domain->getRootValueObjects();

        if ($this->input->hasOption('filter') && $this->input->getOption('filter')) {
            $entities     = $this->filterDomainObjects($entities, $this->input->getOption('filter'));
            $valueObjects = $this->filterDomainObjects($valueObjects, $this->input->getOption('filter'));
        }

        if (!$valueObjects && !$entities) {
            $this->output->error('No entities found under ' . $context->getRootEntityNamespace() . ' namespace');

            return;
        }

        foreach ($entities as $entity) {
            list($repositoryClass, $repositoryShortClassName) = $this->generateRepositoryInterface($context, $entity, $overwrite);
            $this->generateEntityMapper($context, $entity, $overwrite);
            $this->generateRepositoryImplementation($context, $entity, $repositoryClass, $repositoryShortClassName, $overwrite);
        }

        foreach ($valueObjects as $valueObject) {
            $this->generateValueObjectMapper($context, $valueObject, $overwrite);
        }

        $this->output->success('Done!');
    }

    private function generateRepositoryInterface(ScaffoldPersistenceContext $context, DomainObjectStructure $entity, bool $overwrite)
    {
        $entityName        = $entity->getReflection()->getShortName();
        $relativeNamespace = $context->getRelativeObjectNamespace($entity);

        $repositoryName      = 'I' . $entityName . 'Repository';
        $repositoryNamespace = $context->getOutputAbstractNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $repositoryDirectory = $this->namespaceResolver->getDirectoryFor($repositoryNamespace);
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

    private function generateEntityMapper(ScaffoldPersistenceContext $context, DomainObjectStructure $entity, bool $overwrite)
    {
        $entityName        = $entity->getReflection()->getShortName();
        $relativeNamespace = $context->getRelativeObjectNamespace($entity);

        $mapperName      = $entityName . 'Mapper';
        $mapperNamespace = $context->getOutputImplementationNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $mapperDirectory = $this->namespaceResolver->getDirectoryFor($mapperNamespace);

        $mappingCodeContext = $this->generatePropertyBindingCode($context, $entity, 2);

        $php = $this->buildCodeFile(
            __DIR__ . '/Stubs/Persistence/EntityMapper.php.stub',
            $mappingCodeContext,
            [
                '{namespace}'   => $mapperNamespace,
                '{name}'        => $mapperName,
                '{entity}'      => $entity->getDefinition()->getClassName(),
                '{entity_name}' => $entityName,
                '{table_name}'  => \Str::plural(\Str::snake($entityName)),
                '{mapping}'     => $mappingCodeContext->getCode()->getCode(),
            ]
        );

        $this->createFile(PathHelper::combine($mapperDirectory, $mapperName . '.php'), $php, $overwrite);
    }

    protected function generatePropertyBindingCode(ScaffoldPersistenceContext $context, DomainObjectStructure $object, int $indent) : PhpCodeBuilderContext
    {
        $code = new PhpCodeBuilderContext();

        $code->getCode()->indent = $indent;

        foreach ($object->getDefinition()->getProperties() as $property) {
            if ($property->getName() === Entity::ID) {
                continue;
            }

            $this->getCodeGeneratorFor($context->getDomainStructure(), $object, $property->getName())->generatePersistenceMappingCode(
                $context,
                $code,
                $object,
                $property->getName()
            );

            $code->getCode()->appendLine(';');
            $code->getCode()->appendLine();
        }

        return $code;
    }

    private function generateRepositoryImplementation(
        ScaffoldPersistenceContext $context,
        DomainObjectStructure $entity,
        string $interfaceClass,
        string $interfaceName,
        bool $overwrite
    ) {
        $entityName        = $entity->getReflection()->getShortName();
        $relativeNamespace = $context->getRelativeObjectNamespace($entity);

        $repositoryName      = 'Db' . $entityName . 'Repository';
        $repositoryNamespace = $context->getOutputImplementationNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '');
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

    private function generateValueObjectMapper(ScaffoldPersistenceContext $context, DomainObjectStructure $valueObject, bool $overwrite)
    {
        $valueObjectName   = $valueObject->getReflection()->getShortName();
        $relativeNamespace = $context->getRelativeObjectNamespace($valueObject);

        $mapperName      = $valueObjectName . 'Mapper';
        $mapperNamespace = $context->getOutputImplementationNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $mapperDirectory = $this->namespaceResolver->getDirectoryFor($mapperNamespace);

        $mappingCodeContext = $this->generatePropertyBindingCode($context, $valueObject, 2);

        if ($valueObject->hasEntityRelations()) {
            $stubFile = __DIR__ . '/Stubs/Persistence/ValueObjectMapper.php.stub';
        } else {
            $stubFile = __DIR__ . '/Stubs/Persistence/IndependentValueObjectMapper.php.stub';
        }

        $php = $this->buildCodeFile(
            $stubFile,
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