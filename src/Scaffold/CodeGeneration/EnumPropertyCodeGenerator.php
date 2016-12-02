<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Common\Structure\DateTime\Date;
use Dms\Common\Structure\DateTime\DateTime;
use Dms\Common\Structure\DateTime\TimeOfDay;
use Dms\Common\Structure\DateTime\TimezonedDateTime;
use Dms\Core\Model\Object\Enum;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Core\Model\Type\ObjectType;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumPropertyCodeGenerator extends PropertyCodeGenerator
{
    /**
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     *
     * @return bool
     */
    protected function doesSupportProperty(DomainObjectStructure $object, FinalizedPropertyDefinition $property) : bool
    {
        return $property->getType()->nonNullable()->isSubsetOf(Enum::type());
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
        $php = '$map->enum(' . $propertyReference . ')->to(\'' . $columnName . '\')';

        if ($property->getType()->isNullable()) {
            $php .= '->nullable()';
        }

        $php .= '->usingValuesFromConstants()';

        $code->getCode()->append($php);
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

        /** @var ObjectType $objectType */
        $objectType = $property->getType()->nonNullable();
        /** @var string|Enum $enumClass */
        $enumClass = $objectType->getClass();
        $code->addNamespaceImport($enumClass);

        $code->getCode()->appendLine('->enum(' . basename($enumClass) . '::class, [');

        $code->getCode()->indent++;

        foreach ($enumClass::getOptions() as $constant => $option) {
            $label = $this->codeConvention->getCmsFieldLabel((string)$option);
            $code->getCode()->appendLine(basename($enumClass) . '::' . $constant . ' => ' . var_export($label, true) . ',');
        }

        $code->getCode()->indent--;
        $code->getCode()->append('])');

        if (!$property->getType()->isNullable()) {
            $code->getCode()->append('->required()');
        }
    }
}