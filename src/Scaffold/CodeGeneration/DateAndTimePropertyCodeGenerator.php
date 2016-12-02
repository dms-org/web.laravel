<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Common\Structure\DateTime\Date;
use Dms\Common\Structure\DateTime\DateOrTimeObject;
use Dms\Common\Structure\DateTime\DateTime;
use Dms\Common\Structure\DateTime\Persistence\DateMapper;
use Dms\Common\Structure\DateTime\Persistence\DateTimeMapper;
use Dms\Common\Structure\DateTime\Persistence\TimeOfDayMapper;
use Dms\Common\Structure\DateTime\Persistence\TimezonedDateTimeMapper;
use Dms\Common\Structure\DateTime\TimeOfDay;
use Dms\Common\Structure\DateTime\TimezonedDateTime;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateAndTimePropertyCodeGenerator extends PropertyCodeGenerator
{
    /**
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     *
     * @return bool
     */
    protected function doesSupportProperty(DomainObjectStructure $object, FinalizedPropertyDefinition $property) : bool
    {
        return $property->getType()->nonNullable()->isSubsetOf(DateOrTimeObject::type());
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
        $code->getCode()->appendLine('$map->embedded(' . $propertyReference . ')');

        $code->getCode()->indent++;

        if ($property->getType()->isNullable()) {
            $code->getCode()->appendLine('->withIssetColumn(\'' . $columnName . '\')');
        }

        if ($property->getType()->isSubsetOf(DateTime::type())) {
            $class = DateTimeMapper::class;
        } elseif ($property->getType()->isSubsetOf(Date::type())) {
            $class = DateMapper::class;
        } elseif ($property->getType()->isSubsetOf(TimeOfDay::type())) {
            $class = TimeOfDayMapper::class;
        } elseif ($property->getType()->isSubsetOf(TimezonedDateTime::type())) {
            $class = TimezonedDateTimeMapper::class;
            $code->addNamespaceImport($class);
            $code->getCode()->append('->using(new ' . basename($class) . '(\'' . $columnName . '_date_time\', \'' . $columnName . '_timezone\'))');
            return;
        }

        $code->addNamespaceImport($class);
        $code->getCode()->append('->using(new ' . basename($class) . '(\'' . $columnName . '\'))');

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


        if ($property->getType()->isSubsetOf(DateTime::type())) {
            $code->getCode()->append('->dateTime()');
        } elseif ($property->getType()->isSubsetOf(Date::type())) {
            $code->getCode()->append('->date()');
        } elseif ($property->getType()->isSubsetOf(TimeOfDay::type())) {
            $code->getCode()->append('->time()');
        } elseif ($property->getType()->isSubsetOf(TimezonedDateTime::type())) {
            $code->getCode()->append('->dateTimeWithTimezone()');
        }

        if (!$property->getType()->isNullable()) {
            $code->getCode()->appendLine('->required()');
        }
    }
}