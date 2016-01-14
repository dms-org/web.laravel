<?php

namespace Dms\Web\Laravel\Action;

use Dms\Core\Module\IAction;

/**
 * The action input handler interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IActionInputTransformer
{
    /**
     * Transforms for the supplied action.
     *
     * @param IAction $action
     * @param array   $input
     *
     * @return array
     */
    public function transform(IAction $action, array $input);
}