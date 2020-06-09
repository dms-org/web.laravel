<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration\Relation;

use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Web\Laravel\Scaffold\CodeGeneration\PhpCodeBuilderContext;
use Dms\Web\Laravel\Scaffold\CodeGeneration\PropertyCodeGenerator;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;
use Dms\Web\Laravel\Scaffold\Domain\DomainStructure;
use Dms\Web\Laravel\Scaffold\ScaffoldCmsContext;
use Dms\Web\Laravel\Scaffold\ScaffoldPersistenceContext;
use Illuminate\Support\Str;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityPropertyCodeGenerator extends PropertyCodeGenerator
{
    /**
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     *
     * @return bool
     */
    protected function doesSupportProperty(DomainStructure $domain, DomainObjectStructure $object, FinalizedPropertyDefinition $property) : bool
    {
        return $property->getType()->nonNullable()->isSubsetOf(Entity::type());
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
        $relation = $object->getRelation($property->getName());

        $isManyToOne = $relation->hasInverseRelation() && $relation->getInverseRelation()->isToMany();

        $entity = $context->getDomainStructure()->getObject($relation->getRelatedObject()->getDefinition()->getClassName());

        $code->addNamespaceImport($entity->getDefinition()->getClassName());

        if ($isManyToOne) {
            $foreignKeyName = snake_case($entity->getReflection()->getShortName()) . '_id';

            $code->addNamespaceImport($entity->getDefinition()->getClassName());

            $code->getCode()->append('$map->column(\'' . $foreignKeyName . '\')');

            if ($property->getType()->isNullable()) {
                $code->getCode()->append('->nullable()');
            }

            $code->getCode()->append('->asUnsignedInt()');

            $code->getCode()->appendLine(';');
        }

        $code->getCode()->appendLine('$map->relation(' . $propertyReference . ')');

        $code->getCode()->indent++;

        $code->getCode()->appendLine('->to(' . $entity->getReflection()->getShortName() . '::class)');

        if ($isManyToOne) {
            $code->getCode()->appendLine('->manyToOne()');
        } else {
            $code->getCode()->appendLine('->toOne()');

            $isIdentifying = $relation->hasInverseRelation() && !$relation->getInverseRelation()->getDefinition()->getType()->isNullable();

            if ($isIdentifying) {
                $code->getCode()->appendLine('->identifying()');
            }
        }

        if ($relation->hasInverseRelation()) {
            $inverseReference = $this->getPropertyReference(
                $relation->getRelatedObject(),
                $relation->getInverseRelation()->getDefinition()->getName()
            );

            $code->getCode()->appendLine('->withBidirectionalRelation(' . $inverseReference . ')');
        }

        if ($isManyToOne) {
            $code->getCode()->append('->withRelatedIdAs(\'' . $foreignKeyName . '\')');
        } else {
            $foreignKeyName = snake_case($object->getReflection()->getShortName()) . '_id';
            $code->getCode()->append('->withParentIdAs(\'' . $foreignKeyName . '\')');
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
        $relation = $object->getRelation($property->getName());

        $entity = $context->getDomainStructure()->getObject($relation->getRelatedObject()->getDefinition()->getClassName());

        $relativeNamespace      = $context->getRelativeObjectNamespace($entity);
        $dataSourceNamespace    = $context->getDataSourceNamespace() . ($relativeNamespace ? '\\' . $relativeNamespace : '');
        $dataSourceInterface    = $dataSourceNamespace . '\\I' . $entity->getReflection()->getShortName() . 'Repository';
        $dataSourcePropertyName = camel_case($entity->getReflection()->getShortName()) . 'Repository';

        $code->addNamespaceImport($dataSourceInterface);
        $code->addNamespaceImport($entity->getDefinition()->getClassName());
        $dataSourcePropertyName = $code->addConstructorParameter($this->getShortClassName($dataSourceInterface), $dataSourcePropertyName);

        $code->getCode()->appendLine('Field::create(\'' . $fieldName . '\', \'' . $fieldLabel . '\')');
        $code->getCode()->indent++;

        $code->getCode()->appendLine('->entityFrom($this->' . $dataSourcePropertyName . ')');

        if (!$property->getType()->isNullable()) {
            $code->getCode()->appendLine('->required()');
        }

        $code->getCode()->append('->labelledBy(' . $this->findLabelProperty($entity) . ')');

        $code->getCode()->indent--;

    }

    protected function findLabelProperty(DomainObjectStructure $entity) : string
    {
        foreach ($entity->getDefinition()->getProperties() as $property) {
            if (Str::contains(strtolower($property->getName()), ['name', 'title'])) {
                return $this->getPropertyReference($entity, $property->getName());
            }
        }

        return '/* FIXME: */ ' . $this->getPropertyReference($entity, 'id');
    }
}
