<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Scaffold\CodeGeneration;

use Dms\Common\Structure\Web\EmailAddress;
use Dms\Common\Structure\Web\Html;
use Dms\Common\Structure\Web\IpAddress;
use Dms\Common\Structure\Web\Persistence\EmailAddressMapper;
use Dms\Common\Structure\Web\Persistence\HtmlMapper;
use Dms\Common\Structure\Web\Persistence\IpAddressMapper;
use Dms\Common\Structure\Web\Persistence\UrlMapper;
use Dms\Common\Structure\Web\Url;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Web\Laravel\Scaffold\Domain\DomainObjectStructure;
use Dms\Web\Laravel\Scaffold\ScaffoldCmsContext;
use Dms\Web\Laravel\Scaffold\ScaffoldPersistenceContext;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WebPropertyCodeGenerator extends PropertyCodeGenerator
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
        return $type->isSubsetOf(EmailAddress::type())
        || $type->isSubsetOf(Html::type())
        || $type->isSubsetOf(IpAddress::type())
        || $type->isSubsetOf(Url::type());
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

        $type = $property->getType()->nonNullable();

        if ($type->isSubsetOf(EmailAddress::type())) {
            $class = EmailAddressMapper::class;
        } elseif ($type->isSubsetOf(Html::type())) {
            $class = HtmlMapper::class;
        } elseif ($type->isSubsetOf(IpAddress::type())) {
            $class = IpAddressMapper::class;
        } elseif ($type->isSubsetOf(Url::type())) {
            $class = UrlMapper::class;
        }

        $code->addNamespaceImport($class);

        $code->getCode()->indent++;

        if ($property->getType()->isNullable()) {
            $code->getCode()->appendLine('->withIssetColumn(\'' . $columnName . '\')');
        }

        $code->getCode()->append('->using(new ' . basename($class) . '(\'' . $columnName . '\'))');

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
        $code->getCode()->append('Field::create(\'' . $fieldName . '\', \'' . $fieldLabel . '\')');

        $type = $property->getType()->nonNullable();

        if ($type->isSubsetOf(EmailAddress::type())) {
            $code->getCode()->append('->email()');
        } elseif ($type->isSubsetOf(Html::type())) {
            $code->getCode()->append('->html()');
        } elseif ($type->isSubsetOf(IpAddress::type())) {
            $code->getCode()->append('->ipAddress()');
        } elseif ($type->isSubsetOf(Url::type())) {
            $code->getCode()->append('->url()');
        }

        if (!$property->getType()->isNullable()) {
            $code->getCode()->append('->required()');
        }
    }
}