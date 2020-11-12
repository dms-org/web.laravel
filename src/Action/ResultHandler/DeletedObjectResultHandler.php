<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Response;

/**
 * The deleted object action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DeletedObjectResultHandler extends ActionResultHandler
{
    /**
     * @return string|null
     */
    protected function supportedResultType()
    {
        return TypedObject::class;
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
        return $moduleContext->getModule() instanceof IReadModule && $action instanceof IObjectAction && $action->getName() === ICrudModule::REMOVE_ACTION;
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
        /** @var IReadModule $module */
        $module = $moduleContext->getModule();
        $label  = $module->getLabelFor($result);
        $type   = \Str::singular(StringHumanizer::humanize($module->getName()));

        return \response()->json([
            'message'      => "The '{$label}' {$type} has been removed.",
            'message_type' => 'info',
            'redirect'     => $moduleContext->getUrl('dashboard'),
        ]);
    }
}