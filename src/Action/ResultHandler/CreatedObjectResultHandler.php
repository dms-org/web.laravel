<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Common\Crud\Action\Crud\CreateAction;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Http\ModuleRequestRouter;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Response;

/**
 * The created entity action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CreatedObjectResultHandler extends ActionResultHandler
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
        return $moduleContext->getModule() instanceof IReadModule && $action instanceof CreateAction;
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
        /** @var ITypedObject $result */
        $moduleContext = ModuleRequestRouter::currentModuleContext();
        $module        = $moduleContext->getModule();
        $label         = $module->getLabelFor($result);
        $type          = str_singular(StringHumanizer::humanize($module->getName()));

        $canEditObject = $module instanceof ICrudModule && $module->allowsEdit() && $module->getEditAction()->isAuthorized();
        $canViewObject = $module->allowsDetails() && $module->getDetailsAction()->isAuthorized();

        $objectId = $module->getDataSource()->getObjectId($result);

        if ($canEditObject) {
            $redirectUrl = $moduleContext->getUrl('action.form', [ICrudModule::EDIT_ACTION, $objectId]);
        } elseif ($canViewObject) {
            $redirectUrl = $moduleContext->getUrl('action.show', [ICrudModule::DETAILS_ACTION, $objectId]);
        } else {
            $redirectUrl = $moduleContext->getUrl('dashboard');
        }

        return \response()->json([
            'message'      => "The '{$label}' {$type} has been created.",
            'message_type' => 'success',
            'redirect'     => $redirectUrl,
        ]);
    }
}