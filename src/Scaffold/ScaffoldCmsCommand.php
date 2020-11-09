<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Scaffold;

use Dms\Common\Structure\FileSystem\PathHelper;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Web\Laravel\Scaffold\CodeGeneration\PhpCodeBuilderContext;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;

/**
 * The dms:scaffold:persistence command
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ScaffoldCmsCommand extends ScaffoldCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'dms:scaffold:cms {package_name : the name of the package in \'this-format\'}
                            {entity_namespace=App\\Domain\\Entities : The namespace of the entities}
                            {data_source_namespace=App\\Domain\\Services\\Persistence : The namespace of the repositories interfaces}
                            {output_namespace=App\\Cms : The namespace to place cms packages and module}
                            {--overwrite : Whether to overwrite existing files}
                            {--filter= : A filter pattern to restrict which entities are scaffolded e.g App\\Domain\\Entities\\Specific\\*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffolds the CMS layer for a set of entities';


    /**
     * Execute the console command.
     *
     * @throws InvalidOperationException
     */
    public function handle()
    {
        $packageName = $this->input->getArgument('package_name');

        $domain  = $this->domainStructureLoader->loadDomainStructure($this->input->getArgument('entity_namespace'));
        $context = new ScaffoldCmsContext(
            $this->input->getArgument('entity_namespace'),
            $domain,
            $this->input->getArgument('data_source_namespace'),
            $this->input->getArgument('output_namespace')
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

        $modules = [];

        foreach ($entities as $entity) {
            list($moduleName, $moduleClass) = $this->generateModule($entity, $context, $overwrite);

            $modules[$moduleName] = $moduleClass;
        }

        foreach ($valueObjects as $valueObject) {
            $this->generateValueObjectField($valueObject, $context, $overwrite);
        }

        $this->generatePackage($packageName, $context, $modules, $overwrite);

        $this->output->success('Done!');
    }

    private function generateModule(DomainObjectStructure $entity, ScaffoldCmsContext $context, bool $overwrite)
    {
        $entityName        = $entity->getReflection()->getShortName();
        $relativeNamespace = $context->getRelativeObjectNamespace($entity);

        $moduleName                = \Str::snake($entityName, '-');
        $moduleClassName           = $entityName . 'Module';
        $moduleNamespace           = $context->getModuleNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $moduleDirectory           = $this->namespaceResolver->getDirectoryFor($moduleNamespace);
        $moduleDataSourceClassName = 'I' . $entityName . 'Repository';
        $moduleDataSourceClass     = $context->getDataSourceNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '') . '\\' . $moduleDataSourceClassName;

        $fieldCodeContext  = $this->generateFieldBindingsCode($context, $entity, 3);
        $columnCodeContext = $this->generateColumnBindingsCode($context, $entity, 3);

        $fieldCodeContext->addNamespaceImport($entity->getDefinition()->getClassName());

        $php = $this->buildCodeFile(
            __DIR__ . '/Stubs/Cms/Module.php.stub',
            $fieldCodeContext,
            [
                '{namespace}'              => $moduleNamespace,
                '{name}'                   => $moduleName,
                '{class_name}'             => $moduleClassName,
                '{data_source_class}'      => $moduleDataSourceClass,
                '{data_source_class_name}' => $moduleDataSourceClassName,
                '{label_code}'             => $this->generateLabelEntityCode($entity),
                '{fields}'                 => $fieldCodeContext->getCode()->getCode(),
                '{columns}'                => $columnCodeContext->getCode()->getCode(),
            ]
        );

        $this->createFile(PathHelper::combine($moduleDirectory, $moduleClassName . '.php'), $php, $overwrite);

        return [$moduleName, $moduleNamespace . '\\' . $moduleClassName];
    }

    protected function generateLabelEntityCode(DomainObjectStructure $object): string
    {
        $labelProperty = null;

        foreach ($object->getDefinition()->getProperties() as $property) {
            if ($property->getType()->nonNullable()->isSubsetOf(Type::string())) {
                $labelProperty = $property;
                break;
            }
        }

        $code = '$module->labelObjects()';

        if ($labelProperty) {
            $code .= '->fromProperty(' . $object->getPropertyReference($property->getName()) . ');';
        } else {
            $code .= '->fromProperty(/* FIXME: */ ' . $object->getReflection()->getShortName() . '::ID);';
        }

        return $code;
    }

    protected function generateFieldBindingsCode(ScaffoldCmsContext $context, DomainObjectStructure $object, int $indent): PhpCodeBuilderContext
    {
        $code = new PhpCodeBuilderContext();

        $code->getCode()->indent = $indent;

        $code->getCode()->appendLine('$form->section(\'Details\', [');
        $code->getCode()->indent++;

        foreach ($object->getDefinition()->getProperties() as $property) {
            if ($property->getName() === Entity::ID) {
                continue;
            }

            $this->getCodeGeneratorFor($context->getDomainStructure(), $object, $property->getName())->generateCmsFieldBindingCode(
                $context,
                $code,
                $object,
                $property->getName()
            );

            $code->getCode()->appendLine(',');
            $code->getCode()->appendLine('//');
        }

        $code->getCode()->indent--;
        $code->getCode()->appendLine(']);');

        return $code;
    }

    protected function generateColumnBindingsCode(ScaffoldCmsContext $context, DomainObjectStructure $object, int $indent): PhpCodeBuilderContext
    {
        $code = new PhpCodeBuilderContext();

        $code->getCode()->indent = $indent;

        foreach ($object->getDefinition()->getProperties() as $property) {
            if ($property->getName() === Entity::ID) {
                continue;
            }

            $this->getCodeGeneratorFor($context->getDomainStructure(), $object, $property->getName())->generateCmsColumnBindingCode(
                $context,
                $code,
                $object,
                $property->getName()
            );

            $code->getCode()->appendLine(';');
        }

        return $code;
    }

    private function generateValueObjectField(DomainObjectStructure $valueObject, ScaffoldCmsContext $context, bool $overwrite)
    {
        $valueObjectName   = $valueObject->getReflection()->getShortName();
        $relativeNamespace = $context->getRelativeObjectNamespace($valueObject);

        $fieldClassName = $valueObjectName . 'Field';
        $fieldNamespace = $context->getValueObjectFieldNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $fieldDirectory = $this->namespaceResolver->getDirectoryFor($fieldNamespace);

        $fieldCodeContext = $this->generateFieldBindingsCode($context, $valueObject, 2);

        $php = $this->buildCodeFile(
            __DIR__ . '/Stubs/Cms/ValueObjectField.php.stub',
            $fieldCodeContext,
            [
                '{namespace}'               => $fieldNamespace,
                '{class_name}'              => $fieldClassName,
                '{value_object_class}'      => $valueObject->getDefinition()->getClassName(),
                '{value_object_class_name}' => $valueObjectName,
                '{fields}'                  => $fieldCodeContext->getCode()->getCode(),
            ]
        );

        $this->createFile(PathHelper::combine($fieldDirectory, $fieldClassName . '.php'), $php, $overwrite);
    }

    private function generatePackage(string $packageName, ScaffoldCmsContext $context, array $modules, bool $overwrite)
    {
        $packageClassName = \Str::studly($packageName) . 'Package';
        $packageDirectory = $this->namespaceResolver->getDirectoryFor($context->getOutputNamespace());

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Cms/Package.php.stub');

        $moduleImports = [];
        $moduleMap     = [];
        $indent        = str_repeat(' ', 4);

        foreach ($modules as $name => $moduleClass) {
            $moduleImports[] = 'use ' . $moduleClass . ';';
            $moduleName      = last(explode('\\', $moduleClass));
            $moduleMap[]     = $indent . $indent . $indent . '\'' . $name . '\' => ' . $moduleName . '::class,';
        }

        $php = strtr($php, [
            '{namespace}'       => $context->getOutputNamespace(),
            '{name}'            => $packageName,
            '{class_name}'      => $packageClassName,
            '{module_imports}'  => implode(PHP_EOL, $moduleImports),
            '{module_name_map}' => '[' . PHP_EOL . implode(PHP_EOL, $moduleMap) . PHP_EOL . $indent . $indent . ']',
        ]);

        $this->createFile(PathHelper::combine($packageDirectory, $packageClassName . '.php'), $php, $overwrite);
    }
}