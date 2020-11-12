<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ExceptionHandler;

use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Module\IAction;
use Dms\Core\Persistence\Db\Mapping\EntityOutOfSyncException;
use Dms\Web\Laravel\Action\ActionExceptionHandler;
use Dms\Web\Laravel\Http\ModuleContext;
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
     * @return string|null
     */
    protected function supportedExceptionType()
    {
        return EntityOutOfSyncException::class;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param \Exception    $exception
     *
     * @return bool
     */
    protected function canHandleException(ModuleContext $moduleContext, IAction $action, \Exception $exception) : bool
    {
        return true;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param \Exception    $exception
     *
     * @return Response|mixed
     */
    protected function handleException(ModuleContext $moduleContext, IAction $action, \Exception $exception)
    {
        /** @var EntityOutOfSyncException $exception */
        $hasEntityBeenDeleted = !$exception->hasCurrentEntityInDb();
        $entity               = $exception->getEntityBeingPersisted();

        /** @var IReadModule $module */
        $module = $moduleContext->getModule();
        $label  = $module->getLabelFor($entity);
        $type   = \Str::singular(StringHumanizer::humanize($module->getName()));

        // TODO: add options to resave?
        if ($hasEntityBeenDeleted) {
            return \response()->json([
                'message'      => "The '{$label}' {$type} has been removed in another instance.",
                'message_type' => 'danger',
                'redirect'     => $moduleContext->getUrl('dashboard'),
            ], 400);
        } else {
            return \response()->json([
                'message'      => "The '{$label}' {$type} has been updated in another instance.",
                'message_type' => 'warning',
            ], 400);
        }
    }
}