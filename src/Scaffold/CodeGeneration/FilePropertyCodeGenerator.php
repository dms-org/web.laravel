<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Common\Structure\FileSystem\File;
use Dms\Common\Structure\FileSystem\Image;
use Dms\Common\Structure\FileSystem\Persistence\FileMapper;
use Dms\Common\Structure\FileSystem\Persistence\ImageMapper;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FilePropertyCodeGenerator extends PropertyCodeGenerator
{
    /**
     * @param DomainObjectStructure       $object
     * @param FinalizedPropertyDefinition $property
     *
     * @return bool
     */
    protected function doesSupportProperty(DomainObjectStructure $object, FinalizedPropertyDefinition $property) : bool
    {
        return $property->getType()->nonNullable()->isSubsetOf(File::type());
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

        if ($property->getType()->nonNullable()->isSubsetOf(Image::type())) {
            $class = ImageMapper::class;
        } else {
            $class = FileMapper::class;
        }

        $code->addNamespaceImport($class);
        $basePath = $this->getStorageDirectoryCode($object);
        $code->getCode()->append('->using(new ' . basename($class) . '(\'' . $columnName . '\', \'' . $columnName . '_file_name\', ' . $basePath . '))');

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
        $code->getCode()->appendLine('Field::create(\'' . $fieldName . '\', \'' . $fieldLabel . '\')');

        $code->getCode()->indent++;

        if ($property->getType()->nonNullable()->isSubsetOf(Image::type())) {
            $code->getCode()->appendLine('->image()');
        } else {
            $code->getCode()->appendLine('->file()');
        }

        if (!$property->getType()->isNullable()) {
            $code->getCode()->appendLine('->required()');
        }

        $code->getCode()->append('->moveToPathWithRandomFileName(' . $this->getStorageDirectoryCode($object) . ')');

        $code->getCode()->indent--;
    }

    protected function getStorageDirectoryCode(DomainObjectStructure $object) : string
    {
        return 'public_path(\'app/' . snake_case($object->getReflection()->getShortName()) . '\')';
    }
}