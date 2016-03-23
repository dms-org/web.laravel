<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\Http\ModuleContext;
use Illuminate\Http\Response;

/**
 * The null action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NullResultHandler extends ActionResultHandler
{

    /**
     * @return string|null
     */
    protected function supportedResultType()
    {
        return null;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param mixed         $result
     *
     * @return bool
     */
    protected function canHandleResult(ModuleContext $moduleContext, IAction $action, $result) : bool
    {
        return $action->getReturnTypeClass() === null;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param mixed         $result
     *
     * @return Response|mixed
     */
    protected function handleResult(ModuleContext $moduleContext, IAction $action, $result)
    {
        return \response()->json([
            'message' => trans('dms::action.generic-response'),
        ]);
    }
}