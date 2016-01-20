<?php

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
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
     * @param IAction $action
     * @param mixed   $result
     *
     * @return bool
     */
    protected function canHandleResult(IAction $action, $result)
    {
        return $action->getReturnTypeClass() === null;
    }

    /**
     * @param IAction $action
     * @param mixed   $result
     *
     * @return Response|mixed
     */
    protected function handleResult(IAction $action, $result)
    {
        return \response()->json([
            'message' => 'The action was successfully executed',
        ]);
    }
}