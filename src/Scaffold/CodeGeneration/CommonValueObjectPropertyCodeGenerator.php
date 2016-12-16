<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Core\Model\Type\CollectionType;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;
use Dms\Web\Laravel\Scaffold\ScaffoldCmsContext;
use Dms\Web\Laravel\Scaffold\ScaffoldPersistenceContext;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class CommonValueObjectPropertyCodeGenerator extends PropertyCodeGenerator
{
    /**
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     *
     * @return bool
     */
    protected function doesSupportProperty(DomainObjectStructure $object, FinalizedPropertyDefinition $property) : bool
    {
        $type = $property->getType()->nonNullable();

        foreach ($this->getSupportedValueObjectClasses() as $class) {
            if ($type->isSubsetOf($class::type()) || $type->isSubsetOf($class::collectionType())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    abstract protected function getSupportedValueObjectClasses() : array;

    /**
     * @param ScaffoldPersistenceContext  $context
     * @param PhpCodeBuilderContext       $code
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     * @param string                      $propertyReference
     * @param string                      $columnName
     */
    protected function doGeneratePersistenceMappingCode(
        ScaffoldPersistenceContext $context,
        PhpCodeBuilderContext $code,
        DomainObjectStructure $object,
        FinalizedPropertyDefinition $property,
        string $propertyReference,
        string $columnName
    ) {
        if ($property->getType() instanceof CollectionType) {
            $code->getCode()->appendLine('$map->embeddedCollection(' . $propertyReference . ')');
            $code->getCode()->indent++;
            $code->getCode()->appendLine('->toTable(\'' . snake_case($object->getReflection()->getShortName() . '_' . $property->getName()) . '\')');
            $code->getCode()->appendLine('->withPrimaryKey(\'id\')');
            $code->getCode()->appendLine('->withForeignKeyToParentAs(\'' . snake_case($object->getReflection()->getShortName()) . '_id' . '\')');
        } else {
            $code->getCode()->appendLine('$map->embedded(' . $propertyReference . ')');
            $code->getCode()->indent++;
        }

        $type = $property->getType();
        if ($type instanceof CollectionType) {
            $objectClass = $type->getElementType()->nonNullable()->asTypeString();
        } else {
            $objectClass = $type->nonNullable()->asTypeString();
        }

        $this->doGeneratePersistenceMappingObjectMapperCode($context, $code, $object, $property, $type instanceof CollectionType, $objectClass, $columnName);

        $code->getCode()->indent--;
    }

    abstract protected function doGeneratePersistenceMappingObjectMapperCode(
        ScaffoldPersistenceContext $context,
        PhpCodeBuilderContext $code,
        DomainObjectStructure $object,
        FinalizedPropertyDefinition $property,
        bool $isCollection,
        string $objectClass,
        string $columnName
    );

    /**
     * @param ScaffoldCmsContext          $context
     * @param PhpCodeBuilderContext       $code
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     * @param string                      $propertyReference
     * @param string                      $fieldName
     * @param string                      $fieldLabel
     */
    protected function doGenerateCmsFieldCode(
        ScaffoldCmsContext $context,
        PhpCodeBuilderContext $code,
        DomainObjectStructure $object,
        FinalizedPropertyDefinition $property,
        string $propertyReference,
        string $fieldName,
        string $fieldLabel
    ) {
        if ($property->getType() instanceof CollectionType) {
            $code->getCode()->appendLine('Field::create(\'' . $fieldName . '\', \'' . $fieldLabel . '\')->arrayOf(');
            $code->getCode()->indent++;
            $code->getCode()->append('Field::element()');

        } else {
            $code->getCode()->append('Field::create(\'' . $fieldName . '\', \'' . $fieldLabel . '\')');
        }

        $type = $property->getType();
        if ($type instanceof CollectionType) {
            $objectClass = $type->getElementType()->nonNullable()->asTypeString();
        } else {
            $objectClass = $type->nonNullable()->asTypeString();
        }

        $this->doGenerateCmsObjectFieldCode($context, $code, $object, $property, $type instanceof CollectionType, $objectClass);

        if ($property->getType() instanceof CollectionType) {
            if (!$this->appendsRequiredMethodCall()) {
                $code->getCode()->append('->required()');
            }

            $code->getCode()->indent--;
            $code->getCode()->appendLine();
            $code->getCode()->append(')');
        } else {
            if (!$property->getType()->isNullable() && !$this->appendsRequiredMethodCall()) {
                $code->getCode()->append('->required()');
            }
        }
    }

    protected function appendsRequiredMethodCall() : bool
    {
        return false;
    }

    abstract protected function doGenerateCmsObjectFieldCode(
        ScaffoldCmsContext $context,
        PhpCodeBuilderContext $code,
        DomainObjectStructure $object,
        FinalizedPropertyDefinition $property,
        bool $isCollection,
        string $objectClass
    );
}