<?php

namespace Dms\Web\Laravel\Action;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IAction;
use Illuminate\Http\Response;

/**
 * The action result handler interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IActionResultHandler
{
    /**
     * Gets the class name string for which this result handler can process
     * or null if no specific class is supported.
     *
     * @return string|null
     */
    public function getSupportedResultType();

    /**
     * Returns whether the result handler can handle the supplied result from
     * the supplied action.
     *
     * @param IAction $action
     * @param mixed   $result
     *
     * @return bool
     */
    public function accepts(IAction $action, $result);

    /**
     * Handles the supplied action result and returns the appropriate HTTP response for handling
     * the result.
     *
     * @param IAction $action
     * @param mixed   $result
     *
     * @return Response|mixed
     * @throws InvalidArgumentException
     */
    public function handle(IAction $action, $result);
}