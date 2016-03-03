<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action;

use Dms\Core\Module\IParameterizedAction;
use Dms\Web\Laravel\Http\ModuleContext;

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
     * @param ModuleContext        $moduleContext
     * @param IParameterizedAction $action
     * @param array                $input
     *
     * @return array
     */
    public function transform(ModuleContext $moduleContext,IParameterizedAction $action, array $input) : array;
}