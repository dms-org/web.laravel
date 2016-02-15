<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action;

use Dms\Core\Module\IParameterizedAction;

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
     * @param IParameterizedAction $action
     * @param array                $input
     *
     * @return array
     */
    public function transform(IParameterizedAction $action, array $input) : array;
}