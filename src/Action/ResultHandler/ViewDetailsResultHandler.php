<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Common\Crud\Action\Crud\ViewDetailsAction;
use Dms\Core\Form\IForm;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Renderer\Form\ActionFormRenderer;
use Dms\Web\Laravel\Renderer\Form\FormRenderer;
use Dms\Web\Laravel\Renderer\Form\FormRenderingContext;
use Illuminate\Http\Response;

/**
 * The created entity action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ViewDetailsResultHandler extends ActionResultHandler
{
    /**
     * @var ActionFormRenderer
     */
    protected $formRenderer;

    /**
     * ViewDetailsResultHandler constructor.
     *
     * @param FormRenderer $formRenderer
     */
    public function __construct(FormRenderer $formRenderer)
    {
        parent::__construct();
        $this->formRenderer = $formRenderer;
    }

    /**
     * @return string|null
     */
    protected function supportedResultType()
    {
        return null;
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
        return $action instanceof ViewDetailsAction;
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
        /** @var IForm $result */
        // TODO: handle stage numbers for form context
        return $this->formRenderer->renderFieldsAsValues(new FormRenderingContext($moduleContext, $action), $result);
    }
}