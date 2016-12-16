<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Common\Structure\Money\Currency;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;
use Dms\Web\Laravel\Scaffold\ScaffoldCmsContext;
use Dms\Web\Laravel\Scaffold\ScaffoldPersistenceContext;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CurrencyPropertyCodeGenerator extends PropertyCodeGenerator
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
        return $type->isSubsetOf(Currency::type());
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
        $code->getCode()->append('$map->enum(' . $propertyReference . ')->to(\'' . $columnName . '\')');

        if ($property->getType()->isNullable()) {
            $code->getCode()->append('->nullable()');
        }

        $code->getCode()->append('->asVarchar(3)');
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
        $code->getCode()->append('Field::create(\'' . $fieldName . '\', \'' . $fieldLabel . '\')');

        $code->addNamespaceImport(Currency::class);
        $code->getCode()->append('->enum(Currency::class, Currency::getNameMap())');

        if (!$property->getType()->isNullable()) {
            $code->getCode()->append('->required()');
        }
    }
}