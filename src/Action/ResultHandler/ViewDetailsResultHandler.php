<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Common\Crud\Action\Crud\ViewDetailsAction;
use Dms\Core\Form\IForm;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\Renderer\Form\ActionFormRenderer;
use Dms\Web\Laravel\Renderer\Form\FormRenderer;
use Dms\Web\Laravel\Util\EntityModuleMap;
use Illuminate\Http\Response;

/**
 * The created entity action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ViewDetailsResultHandler extends ActionResultHandler
{
    /**
     * @var EntityModuleMap
     */
    protected $entityModuleMap;

    /**
     * @var ActionFormRenderer
     */
    protected $formRenderer;

    /**
     * CreatedEntityResultHandler constructor.
     *
     * @param EntityModuleMap $entityModuleMap
     * @param FormRenderer    $formRenderer
     */
    public function __construct(EntityModuleMap $entityModuleMap, FormRenderer $formRenderer)
    {
        parent::__construct();
        $this->entityModuleMap = $entityModuleMap;
        $this->formRenderer    = $formRenderer;
    }

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
    protected function canHandleResult(IAction $action, $result) : bool
    {
        return $action instanceof ViewDetailsAction;
    }

    /**
     * @param IAction $action
     * @param mixed   $result
     *
     * @return Response|mixed
     */
    protected function handleResult(IAction $action, $result)
    {
        /** @var IForm $result */
        return $this->formRenderer->renderFieldsAsValues($result);
    }
}