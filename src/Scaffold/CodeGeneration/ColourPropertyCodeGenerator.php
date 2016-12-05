<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Common\Structure\Colour\Colour;
use Dms\Common\Structure\Colour\Mapper\ColourMapper;
use Dms\Common\Structure\Colour\Mapper\TransparentColourMapper;
use Dms\Common\Structure\Colour\TransparentColour;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColourPropertyCodeGenerator extends PropertyCodeGenerator
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
        return $type->isSubsetOf(Colour::type()) || $type->isSubsetOf(TransparentColour::type());
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

        if ($property->getType()->nonNullable()->isSubsetOf(Colour::type())) {
            $class  = ColourMapper::class;
            $method = 'asHexString';
        } else {
            $class  = TransparentColourMapper::class;
            $method = 'asRgbaString';;
        }

        $code->addNamespaceImport($class);
        $code->getCode()->append('->using(' . basename($class) . '::' . $method . '(\'' . $columnName . '\'))');

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

        if ($property->getType()->nonNullable()->isSubsetOf(Colour::type())) {
            $code->getCode()->append('->colour()');
        } else {
            $code->getCode()->append('->colourWithTransparency()');
        }

        if (!$property->getType()->isNullable()) {
            $code->getCode()->append('->required()');
        }
    }
}