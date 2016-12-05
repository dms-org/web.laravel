<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration\Relation;

use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Model\Type\ObjectType;
use Dms\Web\Laravel\Scaffold\CodeGeneration\PhpCodeBuilderContext;
use Dms\Web\Laravel\Scaffold\CodeGeneration\PropertyCodeGenerator;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;
use Dms\Web\Laravel\Scaffold\ScaffoldCmsContext;
use Dms\Web\Laravel\Scaffold\ScaffoldPersistenceContext;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomValueObjectPropertyCodeGenerator extends PropertyCodeGenerator
{
    /**
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     *
     * @return bool
     */
    protected function doesSupportProperty(DomainObjectStructure $object, FinalizedPropertyDefinition $property) : bool
    {
        return $property->getType()->nonNullable()->isSubsetOf(ValueObject::type());
    }

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
        $code->getCode()->appendLine('$map->embedded(' . $propertyReference . ')');

        $code->getCode()->indent++;

        /** @var ObjectType $type */
        $type        = $property->getType()->nonNullable();
        $valueObject = $context->getDomainStructure()->getObject($type->getClass());

        if ($property->getType()->isNullable()) {
            $code->getCode()->appendLine('->withIssetColumn(\'has_' . $columnName . '\')');
        }

        if ($this->hasMultipleValueObjectsOfType($object, $valueObject->getDefinition()->getClassName())) {
            $code->getCode()->appendLine('->withColumnsPrefixedBy(\'' . $columnName . '_\')');
        }

        if ($valueObject->hasEntityRelations()) {
            $code->addNamespaceImport($valueObject->getDefinition()->getClassName());
            $code->getCode()->append('->to(' . $valueObject->getReflection()->getShortName() . '::class)');
        } else {
            $relativeNamespace = $context->getRelativeObjectNamespace($valueObject);
            $mapperNamespace = $context->getOutputImplementationNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '');
            $mapperClass     = $mapperNamespace . '\\' . $valueObject->getReflection()->getShortName() . 'Mapper';

            $code->addNamespaceImport($mapperClass);
            $code->getCode()->append('->using(new ' . basename($mapperClass) . '())');
        }

        $code->getCode()->indent--;
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
    protected function doGenerateCmsFieldCode(
        ScaffoldCmsContext $context,
        PhpCodeBuilderContext $code,
        DomainObjectStructure $object,
        FinalizedPropertyDefinition $property,
        string $propertyReference,
        string $fieldName,
        string $fieldLabel
    ) {
        $isRequired = $property->getType()->isNullable();

        /** @var ObjectType $type */
        $type        = $property->getType()->nonNullable();
        $valueObject = $context->getDomainStructure()->getObject($type->getClass());

        $relativeNamespace = $context->getRelativeObjectNamespace($valueObject);
        $fieldNamespace    = $context->getValueObjectFieldNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $fieldClass        = $fieldNamespace . '\\' . $valueObject->getReflection()->getShortName() . 'Field';

        $code->addNamespaceImport($fieldClass);

        if ($isRequired) {
            $code->getCode()->append('(');
        }

        $code->getCode()->append('new ' . basename($fieldClass) . '(\'' . $fieldName . '\', \'' . $fieldLabel . '\')');

        if ($isRequired) {
            $code->getCode()->append(')->required()');
        }
    }

    private function hasMultipleValueObjectsOfType(DomainObjectStructure $object, string $valueObjectClass) : bool
    {
        $count = 0;

        foreach ($object->getDefinition()->getProperties() as $property) {
            if ($property->getType()->nullable()->isSubsetOf($valueObjectClass::type())) {
                $count++;
            }
        }

        return $count > 1;
    }
}