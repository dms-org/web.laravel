<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Common\Structure\Geo\Country;
use Dms\Common\Structure\Geo\LatLng;
use Dms\Common\Structure\Geo\StreetAddress;
use Dms\Common\Structure\Geo\StreetAddressWithLatLng;
use Dms\Common\Structure\Money\Currency;
use Dms\Common\Structure\Money\Money;
use Dms\Common\Structure\Money\Persistence\MoneyMapper;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MoneyPropertyCodeGenerator extends PropertyCodeGenerator
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
        return $type->isSubsetOf(Currency::type()) || $type->isSubsetOf(Money::type());
    }


    /**
     * @param PhpCodeBuilderContext       $code
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     * @param string                      $propertyReference
     * @param string                      $columnName
     */
    protected function doGeneratePersistenceMappingCode(
        PhpCodeBuilderContext $code,
        DomainObjectStructure $object,
        FinalizedPropertyDefinition $property,
        string $propertyReference,
        string $columnName
    ) {
        $type = $property->getType()->nonNullable();

        if ($type->isSubsetOf(Currency::type())) {
            $code->getCode()->append('$map->enum(' . $propertyReference . ')->to(\'' . $columnName . '\')');

            if ($property->getType()->isNullable()) {
                $code->getCode()->append('->nullable()');
            }

            $code->getCode()->append('->asVarchar(3)');

            return;
        }

        $code->getCode()->appendLine('$map->embedded(' . $propertyReference . ')');

        $code->getCode()->indent++;

        if ($property->getType()->isNullable()) {
            $code->getCode()->appendLine('->withIssetColumn(\'' . $columnName . '_amount\')');
        }

        $code->addNamespaceImport(MoneyMapper::class);
        $code->getCode()->append('->using(new MoneyMapper(\'' . $columnName . '_amount\', \'' . $columnName . '_currency\'))');

        $code->getCode()->indent--;
    }

    /**
     * @param PhpCodeBuilderContext       $code
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     * @param string                      $propertyReference
     * @param string                      $fieldName
     * @param string                      $fieldLabel
     */
    protected function doGenerateCmsFieldCode(
        PhpCodeBuilderContext $code,
        DomainObjectStructure $object,
        FinalizedPropertyDefinition $property,
        string $propertyReference,
        string $fieldName,
        string $fieldLabel
    ) {
        $code->getCode()->append('Field::create(\'' . $fieldName . '\', \'' . $fieldLabel . '\')');

        $type = $property->getType()->nonNullable();

        if ($type->isSubsetOf(Currency::type())) {
            $code->addNamespaceImport(Currency::class);
            $code->getCode()->append('->enum(Currency::class, Currency::getNameMap())');
        } else {
            $code->getCode()->append('->money()');
        }

        if (!$property->getType()->isNullable()) {
            $code->getCode()->append('->required()');
        }
    }
}