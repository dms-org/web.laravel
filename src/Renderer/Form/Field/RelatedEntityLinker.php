<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\IFieldOptions;
use Dms\Web\Laravel\Util\EntityModuleMap;

/**
 * The related entity linker helper class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelatedEntityLinker
{
    /**
     * @param IFieldOptions $options
     *
     * @return callable|null
     */
    public static function getUrlCallbackFor(IFieldOptions $options)
    {
        if (!($options instanceof EntityIdOptions)) {
            return null;
        }

        /** @var EntityModuleMap $entityModuleMap */
        $entityModuleMap = app(EntityModuleMap::class);

        if ($entityModuleMap->hasModuleFor($options->getObjects()->getObjectType())) {
            
            $module = $entityModuleMap->loadModuleFor($options->getObjects()->getObjectType());

            if ($module->getDetailsAction()->isAuthorized()) {
                return function ($id) use ($module) {
                    return route('dms::package.module.action.show', [$module->getPackageName(), $module->getName(), $module->getDetailsAction()->getName(), $id]);
                };
            }
        }

        return null;
    }
}