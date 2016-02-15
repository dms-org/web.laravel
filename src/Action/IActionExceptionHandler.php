<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Action;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IAction;
use Illuminate\Http\Response;

/**
 * The action result handler interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IActionExceptionHandler
{
    /**
     * Gets the exception class name string for which this result handler can process
     * or null if no specific class is supported.
     *
     * @return string|null
     */
    public function getSupportedExceptionType();

    /**
     * Returns whether the result handler can handle the supplied result from
     * the supplied action.
     *
     * @param IAction    $action
     * @param \Exception $exception
     *
     * @return bool
     */
    public function accepts(IAction $action, \Exception $exception) : bool;

    /**
     * Handles the supplied action result and returns the appropriate HTTP response for handling
     * the exception.
     *
     * @param IAction    $action
     * @param \Exception $exception
     *
     * @return Response|mixed
     * @throws InvalidArgumentException
     */
    public function handle(IAction $action, \Exception $exception);
}