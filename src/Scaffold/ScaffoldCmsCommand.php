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
                            {output_namespace=App\\Cms : The namespace to place cms packages and module }
                            {--overwrite : Whether to overwrite existing files}';

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
    public function fire()
    {
        $packageName         = $this->input->getArgument('package_name');
        $namespace           = ltrim($this->input->getArgument('entity_namespace'), '\\');
        $dataSourceNamespace = $this->input->getArgument('data_source_namespace');

        $cmsNamespace              = $this->input->getArgument('output_namespace');
        $moduleNamespace           = $cmsNamespace . '\\Modules';
        $valueObjectFieldNamespace = $cmsNamespace . '\\Modules\\Fields';
        $overwrite                 = (bool)$this->input->hasOption('--overwrite');
        $domain                    = $this->domainStructureLoader->loadDomainStructure($namespace);
        $entities                  = $domain->getRootEntities();
        $valueObjects              = $domain->getRootValueObjects();

        if (!$valueObjects && !$entities) {
            $this->output->error('No entities found under ' . $namespace . ' namespace');

            return;
        }

        $modules = [];

        foreach ($entities as $entity) {
            list($moduleName, $moduleClass) = $this->generateModule($entity, $namespace, $moduleNamespace, $dataSourceNamespace, $overwrite);

            $modules[$moduleName] = $moduleClass;
        }

        foreach ($valueObjects as $valueObject) {
            $this->generateValueObjectField($valueObject, $namespace, $valueObjectFieldNamespace, $overwrite);
        }

        $this->generatePackage($packageName, $cmsNamespace, $modules, $overwrite);

        $this->output->success('Done!');
    }

    private function generateModule(DomainObjectStructure $entity, string $rootEntityNamespace, string $moduleNamespace, string $dataSourceNamespace, bool $overwrite)
    {
        $entityName        = $entity->getReflection()->getShortName();
        $entityNamespace   = $entity->getReflection()->getNamespaceName();
        $relativeNamespace = trim(substr($entityNamespace, strlen($rootEntityNamespace)), '\\');

        $moduleName                = snake_case($entityName, '-');
        $moduleClassName           = $entityName . 'Module';
        $moduleNamespace           = $moduleNamespace . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $moduleDirectory           = $this->namespaceResolver->getDirectoryFor($moduleNamespace);
        $moduleDataSourceClassName = 'I' . $entityName . 'Repository';
        $moduleDataSourceClass     = $dataSourceNamespace . '\\' . $moduleDataSourceClassName;

        $fieldCodeContext  = $this->generateFieldBindingsCode($entity, 3);
        $columnCodeContext = $this->generateColumnBindingsCode($entity, 3);

        $php = $this->buildCodeFile(
            __DIR__ . '/Stubs/Cms/Module.php.stub',
            $fieldCodeContext,
            [
                '{namespace}'              => $moduleNamespace,
                '{name}'                   => $moduleName,
                '{class_name}'             => $moduleClassName,
                '{data_source_class}'      => $moduleDataSourceClass,
                '{data_source_class_name}' => $moduleDataSourceClassName,
                '{fields}'                 => $fieldCodeContext->getCode()->getCode(),
                '{columns}'                => $columnCodeContext->getCode()->getCode(),
            ]
        );

        $this->createFile(PathHelper::combine($moduleDirectory, $moduleClassName . '.php'), $php, $overwrite);

        return [$moduleName, $moduleNamespace . '\\' . $moduleClassName];
    }

    protected function generateFieldBindingsCode(DomainObjectStructure $object, int $indent) : PhpCodeBuilderContext
    {
        $code = new PhpCodeBuilderContext();

        $code->getCode()->indent = $indent;

        $code->getCode()->appendLine('$form->section(\'Details\', [');
        $code->getCode()->indent++;

        foreach ($object->getDefinition()->getProperties() as $property) {
            if ($property->getName() === Entity::ID) {
                continue;
            }

            $this->getCodeGeneratorFor($object, $property->getName())->generateCmsFieldBindingCode(
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

    protected function generateColumnBindingsCode(DomainObjectStructure $object, int $indent) : PhpCodeBuilderContext
    {
        $code = new PhpCodeBuilderContext();

        $code->getCode()->indent = $indent;

        foreach ($object->getDefinition()->getProperties() as $property) {
            if ($property->getName() === Entity::ID) {
                continue;
            }

            $this->getCodeGeneratorFor($object, $property->getName())->generateCmsColumnBindingCode(
                $code,
                $object,
                $property->getName()
            );

            $code->getCode()->appendLine(';');
        }

        return $code;
    }

    private function generateValueObjectField(DomainObjectStructure $valueObject, string $rootEntityNamespace, string $fieldNamespace, bool $overwrite)
    {
        $valueObjectName      = $valueObject->getReflection()->getShortName();
        $valueObjectNamespace = $valueObject->getReflection()->getNamespaceName();
        $relativeNamespace    = trim(substr($valueObjectNamespace, strlen($rootEntityNamespace)), '\\');

        $fieldClassName = $valueObjectName . 'Field';
        $fieldNamespace = $fieldNamespace . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $fieldDirectory = $this->namespaceResolver->getDirectoryFor($fieldNamespace);

        $fieldCodeContext = $this->generateFieldBindingsCode($valueObject, 2);

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

    private function generatePackage(string $packageName, string $cmsNamespace, array $modules, bool $overwrite)
    {
        $packageClassName = studly_case($packageName) . 'Package';
        $packageDirectory = $this->namespaceResolver->getDirectoryFor($cmsNamespace);

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Cms/Package.php.stub');

        $moduleImports = [];
        $moduleMap     = [];
        $indent        = str_repeat(' ', 4);

        foreach ($modules as $name => $moduleClass) {
            $moduleImports[] = 'use ' . $moduleClass . ';';
            $moduleName      = array_last(explode('\\', $moduleClass));
            $moduleMap[]     = $indent . $indent . $indent . '\'' . $name . '\' => ' . $moduleName . '::class,';
        }

        $php = strtr($php, [
            '{namespace}'       => $cmsNamespace,
            '{name}'            => $packageName,
            '{class_name}'      => $packageClassName,
            '{module_imports}'  => implode(PHP_EOL, $moduleImports),
            '{module_name_map}' => '[' . PHP_EOL . implode(PHP_EOL, $moduleMap) . PHP_EOL . $indent . $indent . ']',
        ]);

        $this->createFile(PathHelper::combine($packageDirectory, $packageClassName . '.php'), $php, $overwrite);
    }
}