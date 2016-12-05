<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Common\Structure\Field;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Web\Laravel\Scaffold\CodeGeneration\Convention\CodeConvention;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;
use Dms\Web\Laravel\Scaffold\ScaffoldCmsContext;
use Dms\Web\Laravel\Scaffold\ScaffoldPersistenceContext;

/**
 * The code generator for domain object properties base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class PropertyCodeGenerator
{
    /**
     * @var CodeConvention
     */
    protected $codeConvention;

    /**
     * PropertyCodeGenerator constructor.
     *
     * @param CodeConvention $codeConvention
     */
    public function __construct(CodeConvention $codeConvention)
    {
        $this->codeConvention = $codeConvention;
    }

    /**
     * @param DomainObjectStructure $object
     * @param string                $propertyName
     *
     * @return bool
     */
    final public function supports(DomainObjectStructure $object, string $propertyName) : bool
    {
        return $this->doesSupportProperty($object, $object->getDefinition()->getProperty($propertyName));
    }

    /**
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     *
     * @return bool
     */
    abstract protected function doesSupportProperty(DomainObjectStructure $object, FinalizedPropertyDefinition $property) : bool;

    /**
     * @param ScaffoldPersistenceContext $context
     * @param PhpCodeBuilderContext      $code
     * @param DomainObjectStructure      $object
     * @param string                     $propertyName
     *
     * @throws InvalidArgumentException
     */
    final public function generatePersistenceMappingCode(ScaffoldPersistenceContext $context, PhpCodeBuilderContext $code, DomainObjectStructure $object, string $propertyName)
    {
        if (!$this->supports($object, $propertyName)) {
            throw InvalidArgumentException::format('Invalid property supplied to %s', __METHOD__);
        }

        $this->doGeneratePersistenceMappingCode(
            $context,
            $code,
            $object,
            $object->getDefinition()->getProperty($propertyName),
            $this->getPropertyReference($object, $propertyName),
            $this->codeConvention->getPersistenceColumnName($propertyName)
        );
    }

    /**
     * @param ScaffoldPersistenceContext  $context
     * @param PhpCodeBuilderContext       $code
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     * @param string                      $propertyReference
     * @param string                      $columnName
     */
    abstract protected function doGeneratePersistenceMappingCode(
        ScaffoldPersistenceContext $context,
        PhpCodeBuilderContext $code,
        DomainObjectStructure $object,
        FinalizedPropertyDefinition $property,
        string $propertyReference,
        string $columnName
    );

    /**
     * @param ScaffoldCmsContext    $context
     * @param PhpCodeBuilderContext $code
     * @param DomainObjectStructure $object
     * @param string                $propertyName
     *
     * @throws InvalidArgumentException
     */
    final public function generateCmsFieldBindingCode(ScaffoldCmsContext $context, PhpCodeBuilderContext $code, DomainObjectStructure $object, string $propertyName)
    {
        if (!$this->supports($object, $propertyName)) {
            throw InvalidArgumentException::format('Invalid property supplied to %s', __METHOD__);
        }

        $code->getCode()->appendLine('$form->field(');
        $code->getCode()->indent++;

        $this->generateCmsFieldCode($context, $code, $object, $propertyName);

        $code->getCode()->indent--;
        $code->getCode()->appendLine();
        $code->getCode()->append(')->bindToProperty(' . $this->getPropertyReference($object, $propertyName) . ')');
    }

    /**
     * @param ScaffoldCmsContext    $context
     * @param PhpCodeBuilderContext $code
     * @param DomainObjectStructure $object
     * @param string                $propertyName
     *
     * @throws InvalidArgumentException
     */
    final public function generateCmsColumnBindingCode(ScaffoldCmsContext $context, PhpCodeBuilderContext $code, DomainObjectStructure $object, string $propertyName)
    {
        if (!$this->supports($object, $propertyName)) {
            throw InvalidArgumentException::format('Invalid property supplied to %s', __METHOD__);
        }

        $code->getCode()->append('$table->mapProperty(' . $this->getPropertyReference($object, $propertyName) . ')->to(');

        $this->generateCmsFieldCode($context, $code, $object, $propertyName);

        $code->getCode()->append(')');
    }

    /**
     * @param ScaffoldCmsContext    $context
     * @param PhpCodeBuilderContext $code
     * @param DomainObjectStructure $object
     * @param string                $propertyName
     *
     * @throws InvalidArgumentException
     */
    final public function generateCmsFieldCode(ScaffoldCmsContext $context, PhpCodeBuilderContext $code, DomainObjectStructure $object, string $propertyName)
    {
        if (!$this->supports($object, $propertyName)) {
            throw InvalidArgumentException::format('Invalid property supplied to %s', __METHOD__);
        }

        $code->addNamespaceImport($object->getDefinition()->getClassName());
        $code->addNamespaceImport(Field::class);

        $this->doGenerateCmsFieldCode(
            $context,
            $code,
            $object,
            $object->getDefinition()->getProperty($propertyName),
            $this->getPropertyReference($object, $propertyName),
            $this->codeConvention->getCmsFieldName($propertyName),
            $this->codeConvention->getCmsFieldLabel($propertyName)
        );
    }

    /**
     * @param ScaffoldCmsContext          $context
     * @param PhpCodeBuilderContext       $code
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     * @param string                      $propertyReference
     * @param string                      $fieldName
     * @param string                      $fieldLabel
     */
    abstract protected function doGenerateCmsFieldCode(
        ScaffoldCmsContext $context,
        PhpCodeBuilderContext $code,
        DomainObjectStructure $object,
        FinalizedPropertyDefinition $property,
        string $propertyReference,
        string $fieldName,
        string $fieldLabel
    );

    /**
     * @param DomainObjectStructure $object
     * @param string                $propertyName
     *
     * @return string
     */
    protected function getPropertyReference(DomainObjectStructure $object, string $propertyName) : string
    {
        return $object->getPropertyReference($propertyName);
    }

    /**
     * @param string $class
     *
     * @return string
     */
    protected function getShortClassName(string $class) : string
    {
        return (new \ReflectionClass($class))->getShortName();
    }
}