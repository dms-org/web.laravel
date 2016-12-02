<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Web\Laravel\Util\PhpBuilder;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PhpCodeBuilderContext
{
    /**
     * @var PhpBuilder
     */
    protected $code;

    /**
     * @var string[]
     */
    protected $namespaceImports = [];

    /**
     * @var string[]
     */
    protected $constructorParameters = [];

    /**
     * PhpCodeBuilderContext constructor.
     *
     * @param PhpBuilder $code
     */
    public function __construct(PhpBuilder $code = null)
    {
        $this->code = $code ?? new PhpBuilder();
    }

    /**
     * @return PhpBuilder
     */
    public function getCode(): PhpBuilder
    {
        return $this->code;
    }

    /**
     * @return string[]
     */
    public function getNamespaceImports(): array
    {
        return $this->namespaceImports;
    }

    /**
     * @return string[]
     */
    public function getConstructorParameters(): array
    {
        return $this->constructorParameters;
    }

    /**
     * @param string $import
     */
    public function addNamespaceImport(string $import)
    {
        $this->namespaceImports[$import] = $import;
    }

    /**
     * @param string $class
     * @param string $name
     */
    public function addConstructorParameter(string $class, string $name)
    {
        $this->constructorParameters[$class] = $name;
    }
}