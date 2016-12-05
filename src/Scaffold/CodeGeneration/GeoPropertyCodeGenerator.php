<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Common\Structure\DateTime\Date;
use Dms\Common\Structure\DateTime\DateTime;
use Dms\Common\Structure\DateTime\Persistence\TimezonedDateTimeMapper;
use Dms\Common\Structure\DateTime\TimeOfDay;
use Dms\Common\Structure\DateTime\TimezonedDateTime;
use Dms\Common\Structure\Geo\Country;
use Dms\Common\Structure\Geo\LatLng;
use Dms\Common\Structure\Geo\Persistence\LatLngMapper;
use Dms\Common\Structure\Geo\Persistence\StreetAddressMapper;
use Dms\Common\Structure\Geo\Persistence\StreetAddressWithLatLngMapper;
use Dms\Common\Structure\Geo\StreetAddress;
use Dms\Common\Structure\Geo\StreetAddressWithLatLng;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GeoPropertyCodeGenerator extends PropertyCodeGenerator
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
        return $type->isSubsetOf(Country::type())
        || $type->isSubsetOf(LatLng::type())
        || $type->isSubsetOf(StreetAddress::type())
        || $type->isSubsetOf(StreetAddressWithLatLng::type());
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

        if ($type->isSubsetOf(Country::type())) {
            $code->getCode()->append('$map->enum(' . $propertyReference . ')->to(\'' . $columnName . '\')');

            if ($property->getType()->isNullable()) {
                $code->getCode()->append('->nullable()');
            }

            $code->getCode()->append('->asVarchar(2)');

            return;
        }

        $code->getCode()->appendLine('$map->embedded(' . $propertyReference . ')');

        if ($type->isSubsetOf(LatLng::type())) {
            $class   = LatLngMapper::class;
            $columns = [$columnName . '_lat', $columnName . '_lng'];
        } elseif ($type->isSubsetOf(StreetAddress::type())) {
            $class   = StreetAddressMapper::class;
            $columns = [$columnName];
        } elseif ($type->isSubsetOf(StreetAddressWithLatLng::type())) {
            $class   = StreetAddressWithLatLngMapper::class;
            $columns = [$columnName . '_address', $columnName . '_lat', $columnName . '_lng'];
        }

        foreach ($columns as $key => $column) {
            $columns[$key] = '\'' . $column . '\'';
        }

        $code->getCode()->indent++;

        if ($property->getType()->isNullable()) {
            $code->getCode()->appendLine('->withIssetColumn(' . reset($columns) . ')');
        }

        $code->addNamespaceImport($class);
        $code->getCode()->append('->using(new ' . basename($class) . '(' . implode(', ', $columns) . '))');

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

        if ($type->isSubsetOf(Country::type())) {
            $code->addNamespaceImport(Country::class);
            $code->getCode()->append('->enum(Country::class, Country::getShortNameMap())');
        } else {
            if ($type->isSubsetOf(LatLng::type())) {
                $code->getCode()->append('->latLng()');
            } elseif ($type->isSubsetOf(StreetAddress::type())) {
                $code->getCode()->append('->streetAddress()');
            } elseif ($type->isSubsetOf(StreetAddressWithLatLng::type())) {
                $code->getCode()->append('->streetAddressWithLatLng()');
            }
        }

        if (!$property->getType()->isNullable()) {
            $code->getCode()->append('->required()');
        }
    }
}