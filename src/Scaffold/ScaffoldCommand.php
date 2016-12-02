<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Web\Laravel\Scaffold\CodeGeneration\Convention\DefaultCodeConvention;
use Dms\Web\Laravel\Scaffold\CodeGeneration\DateAndTimePropertyCodeGenerator;
use Dms\Web\Laravel\Scaffold\CodeGeneration\EnumPropertyCodeGenerator;
use Dms\Web\Laravel\Scaffold\CodeGeneration\FallbackPropertyCodeGenerator;
use Dms\Web\Laravel\Scaffold\CodeGeneration\FilePropertyCodeGenerator;
use Dms\Web\Laravel\Scaffold\CodeGeneration\PhpCodeBuilderContext;
use Dms\Web\Laravel\Scaffold\CodeGeneration\PropertyCodeGenerator;
use Dms\Web\Laravel\Scaffold\CodeGeneration\ScalarPropertyCodeGenerator;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;
use Dms\Web\Laravel\Scaffold\Domain\DomainStructureLoader;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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
     * @var NamespaceDirectoryResolver
     */
    protected $namespaceResolver;

    /**
     * @var PropertyCodeGenerator[]
     */
    protected $propertyCodeGenerators;

    /**
     * ScaffoldCommand constructor.
     *
     * @param Filesystem                 $filesystem
     * @param DomainStructureLoader      $domainStructureLoader
     * @param NamespaceDirectoryResolver $namespaceResolver
     */
    public function __construct(Filesystem $filesystem, DomainStructureLoader $domainStructureLoader, NamespaceDirectoryResolver $namespaceResolver)
    {
        parent::__construct();

        $this->filesystem            = $filesystem;
        $this->domainStructureLoader = $domainStructureLoader;
        $this->namespaceResolver     = $namespaceResolver;

        $convention                   = new DefaultCodeConvention();
        $this->propertyCodeGenerators = [
            new ScalarPropertyCodeGenerator($convention),
            new DateAndTimePropertyCodeGenerator($convention),
            new FilePropertyCodeGenerator($convention),
            new EnumPropertyCodeGenerator($convention),
            new FallbackPropertyCodeGenerator($convention),
        ];
    }

    protected function createFile(string $filePath, string $code, bool $overwrite)
    {
        $this->filesystem->makeDirectory(dirname($filePath), 0755, true, true);

        if (!$overwrite && $this->filesystem->exists($filePath)) {
            return;
        }

        $this->filesystem->put($filePath, $code);
    }

    /**
     * @param DomainObjectStructure $object
     * @param string                $propertyName
     *
     * @return PropertyCodeGenerator
     * @throws InvalidArgumentException
     */
    protected function getCodeGeneratorFor(DomainObjectStructure $object, string $propertyName) : PropertyCodeGenerator
    {
        foreach ($this->propertyCodeGenerators as $codeGenerator) {
            if ($codeGenerator->supports($object, $propertyName)) {
                return $codeGenerator;
            }
        }

        throw InvalidArgumentException::format('Cannot find property generator for \'%s\'', $propertyName);
    }

    /**
     * @param string                $stubFile
     * @param PhpCodeBuilderContext $code
     * @param array                 $replacements
     *
     * @return string
     */
    protected function buildCodeFile(string $stubFile, PhpCodeBuilderContext $code, array $replacements)
    {
        $php = $this->filesystem->get($stubFile);

        $imports               = [];
        $properties            = [];
        $constructorParameters = [];
        $initializers          = [];

        foreach ($code->getNamespaceImports() as $import) {
            $imports[] = 'use ' . $import . ';';
        }

        foreach ($code->getConstructorParameters() as $classType => $name) {
            $indent = '    ';

            $property = $indent . '/**';
            $property .= $indent . '* @var ' . basename($classType);
            $property .= $indent . '*/';
            $property .= $indent . 'protected $' . $name;

            $properties[]            = $property;
            $constructorParameters[] = $classType . ' $' . $name;
            $initializers[]          = $indent . $indent . '$this->' . $name . ' = $' . $name;
        }

        $php = strtr($php,
            [
                '{imports}'            => implode(PHP_EOL, $imports),
                '{properties}'         => implode(PHP_EOL, $properties),
                '{constructor_params}' => $constructorParameters ? ', ' . implode(', ', $constructorParameters) : '',
                '{initializers}'       => implode(PHP_EOL, $initializers),
            ] + $replacements
        );

        return $php;
    }
}