<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold;

use Dms\Common\Structure\FileSystem\PathHelper;
use Dms\Core\Exception\InvalidOperationException;

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
                            {output_dir=app/Cms : The path to place cms packages and module }
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

        $cmsDirectory              = $this->input->getArgument('output_dir');
        $moduleDirectory           = $cmsDirectory . '/Modules';
        $valueObjectFieldDirectory = $cmsDirectory . '/Modules/Fields';
        $overwrite                 = (bool)$this->input->hasOption('--overwrite');
        $entities                  = $this->domainStructureLoader->getAllRootEntitiesUnder($namespace);
        $valueObjects              = $this->domainStructureLoader->getAllRootValueObjectsUnder($namespace);

        if (!$valueObjects && !$entities) {
            $this->output->error('No entities found under ' . $namespace . ' namespace');

            return;
        }

        $modules = [];

        foreach ($entities as $entity) {
            list($moduleName, $moduleClass) = $this->generateModule($entity, $namespace, $moduleDirectory, $dataSourceNamespace, $overwrite);

            $modules[$moduleName] = $moduleClass;
        }

        foreach ($valueObjects as $valueObject) {
            $this->generateValueObjectField($valueObject, $namespace, $valueObjectFieldDirectory, $overwrite);
        }

        $this->generatePackage($packageName, $cmsDirectory, $modules, $overwrite);

        $this->output->success('Done!');
    }

    private function generateModule(string $entity, string $rootEntityNamespace, string $moduleDirectory, string $dataSourceNamespace, bool $overwrite)
    {
        $entityName        = (new \ReflectionClass($entity))->getShortName();
        $entityNamespace   = (new \ReflectionClass($entity))->getNamespaceName();
        $relativeNamespace = trim(substr($entityNamespace, strlen($rootEntityNamespace)), '\\');

        $moduleName                = snake_case($entityName, '-');
        $moduleClassName           = $entityName . 'Module';
        $moduleNamespace           = $this->namespaceResolver->getNamespaceFor($this->getAbsolutePath($moduleDirectory)) . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $moduleDirectory           = PathHelper::combine($moduleDirectory, $relativeNamespace);
        $moduleDataSourceClassName = 'I' . $entityName . 'Repository';
        $moduleDataSourceClass     = $dataSourceNamespace . '\\' . $moduleDataSourceClassName;

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Cms/Module.php.stub');

        $php = strtr($php, [
            '{namespace}'              => $moduleNamespace,
            '{name}'                   => $moduleName,
            '{class_name}'             => $moduleClassName,
            '{data_source_class}'      => $moduleDataSourceClass,
            '{data_source_class_name}' => $moduleDataSourceClassName,
        ]);

        $this->createFile(PathHelper::combine($moduleDirectory, $moduleClassName . '.php'), $php, $overwrite);

        return [$moduleName, $moduleNamespace . '\\' . $moduleClassName];
    }

    private function generateValueObjectField(string $valueObject, string $rootEntityNamespace, string $fieldDirectory, bool $overwrite)
    {
        $valueObjectName      = (new \ReflectionClass($valueObject))->getShortName();
        $valueObjectNamespace = (new \ReflectionClass($valueObject))->getNamespaceName();
        $relativeNamespace    = trim(substr($valueObjectNamespace, strlen($rootEntityNamespace)), '\\');

        $fieldClassName = $valueObjectName . 'Field';
        $fieldNamespace = $this->namespaceResolver->getNamespaceFor($this->getAbsolutePath($fieldDirectory)) . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $fieldDirectory = PathHelper::combine($fieldDirectory, $relativeNamespace);

        $php = $this->filesystem->get(__DIR__ . '/Stubs/Cms/ValueObjectField.php.stub');

        $php = strtr($php, [
            '{namespace}'               => $fieldNamespace,
            '{class_name}'              => $fieldClassName,
            '{value_object_class}'      => $valueObject,
            '{value_object_class_name}' => $valueObjectName,
        ]);

        $this->createFile(PathHelper::combine($fieldDirectory, $fieldClassName . '.php'), $php, $overwrite);
    }

    private function generatePackage(string $packageName, string $cmsDirectory, array $modules, bool $overwrite)
    {
        $packageClassName = studly_case($packageName) . 'Package';
        $packageNamespace = $this->namespaceResolver->getNamespaceFor($this->getAbsolutePath($cmsDirectory));

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
            '{namespace}'       => $packageNamespace,
            '{name}'            => $packageName,
            '{class_name}'      => $packageClassName,
            '{module_imports}'  => implode(PHP_EOL, $moduleImports),
            '{module_name_map}' => '[' . PHP_EOL . implode(PHP_EOL, $moduleMap) . PHP_EOL . $indent . $indent . ']',
        ]);

        $this->createFile(PathHelper::combine($cmsDirectory, $packageClassName . '.php'), $php, $overwrite);
    }
}