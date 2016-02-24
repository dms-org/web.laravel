<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Action\ExceptionHandler;

use Dms\Core\Auth\UserForbiddenException;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionExceptionHandler;
use Illuminate\Http\Response;

/**
 * The user forbidden exception handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UserForbiddenExceptionHandler extends ActionExceptionHandler
{
    /**
     * @return string|null
     */
    protected function supportedExceptionType()
    {
        return UserForbiddenException::class;
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
        return \response()->json([
            'message' => 'The current account is forbidden from running this action',
        ], 403);
    }
}