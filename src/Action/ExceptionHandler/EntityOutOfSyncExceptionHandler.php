<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ExceptionHandler;

use Dms\Core\Module\IAction;
use Dms\Core\Persistence\Db\Mapping\EntityOutOfSyncException;
use Dms\Web\Laravel\Action\ActionExceptionHandler;
use Dms\Web\Laravel\Util\EntityModuleMap;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Response;

/**
 * The entity out of sync exception handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityOutOfSyncExceptionHandler extends ActionExceptionHandler
{
    /**
     * @var EntityModuleMap
     */
    protected $entityModuleMap;

    /**
     * EntityOutOfSyncExceptionHandler constructor.
     *
     * @param EntityModuleMap $entityModuleMap
     */
    public function __construct(EntityModuleMap $entityModuleMap)
    {
        parent::__construct();
        $this->entityModuleMap = $entityModuleMap;
    }

    /**
     * @return string|null
     */
    protected function supportedExceptionType()
    {
        return EntityOutOfSyncException::class;
    }

    /**
     * @param IAction    $action
     * @param \Exception $exception
     *
     * @return bool
     */
    protected function canHandleException(IAction $action, \Exception $exception) : bool
    {
        return true;
    }

    /**
     * @param IAction    $action
     * @param \Exception $exception
     *
     * @return Response|mixed
     */
    protected function handleException(IAction $action, \Exception $exception)
    {
        /** @var EntityOutOfSyncException $exception */
        $hasEntityBeenDeleted = !$exception->hasCurrentEntityInDb();
        $entity               = $exception->getEntityBeingPersisted();

        $module = $this->entityModuleMap->loadModuleFor(get_class($entity));
        $label  = $module->getLabelFor($entity);
        $type   = str_singular(StringHumanizer::humanize($module->getName()));

        // TODO: add options to resave?
        if ($hasEntityBeenDeleted) {
            return \response()->json([
                'message'      => "The '{$label}' {$type} has been removed in another instance.",
                'message_type' => 'danger',
                'redirect'     => route('dms::package.module.dashboard', [$module->getPackageName(), $module->getName()]),
            ], 400);
        } else {
            return \response()->json([
                'message'      => "The '{$label}' {$type} has been updated in another instance.",
                'message_type' => 'warning',
            ], 400);
        }
    }
}