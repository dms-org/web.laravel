<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package\Module;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\Field\Type\ObjectIdType;
use Dms\Core\Form\IForm;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\InvalidInputException;
use Dms\Core\ICms;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Module\ActionNotFoundException;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IModule;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Module\IUnparameterizedAction;
use Dms\Web\Laravel\Action\ActionExceptionHandlerCollection;
use Dms\Web\Laravel\Action\ActionInputTransformerCollection;
use Dms\Web\Laravel\Action\ActionResultHandlerCollection;
use Dms\Web\Laravel\Action\UnhandleableActionExceptionException;
use Dms\Web\Laravel\Action\UnhandleableActionResultException;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Renderer\Action\ObjectActionButtonBuilder;
use Dms\Web\Laravel\Renderer\Form\ActionFormRenderer;
use Dms\Web\Laravel\Renderer\Form\FormRenderingContext;
use Dms\Web\Laravel\Renderer\Form\IFieldRendererWithActions;
use Dms\Web\Laravel\Util\ActionSafetyChecker;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;

/**
 * The action controller
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionController extends DmsController
{
    /**
     * @var ILanguageProvider
     */
    protected $lang;

    /**
     * @var ActionInputTransformerCollection
     */
    protected $inputTransformers;

    /**
     * @var ActionResultHandlerCollection
     */
    protected $resultHandlers;

    /**
     * @var ActionExceptionHandlerCollection
     */
    protected $exceptionHandlers;

    /**
     * @var ActionSafetyChecker
     */
    protected $actionSafetyChecker;

    /**
     * @var ActionFormRenderer
     */
    protected $actionFormRenderer;

    /**
     * @var ObjectActionButtonBuilder
     */
    protected $actionButtonBuilder;

    /**
     * ActionController constructor.
     *
     * @param ICms                             $cms
     * @param ActionInputTransformerCollection $inputTransformers
     * @param ActionResultHandlerCollection    $resultHandlers
     * @param ActionExceptionHandlerCollection $exceptionHandlers
     * @param ActionSafetyChecker              $actionSafetyChecker
     * @param ActionFormRenderer               $actionFormRenderer
     * @param ObjectActionButtonBuilder        $actionButtonBuilder
     */
    public function __construct(
        ICms $cms,
        ActionInputTransformerCollection $inputTransformers,
        ActionResultHandlerCollection $resultHandlers,
        ActionExceptionHandlerCollection $exceptionHandlers,
        ActionSafetyChecker $actionSafetyChecker,
        ActionFormRenderer $actionFormRenderer,
        ObjectActionButtonBuilder $actionButtonBuilder
    )
    {
        parent::__construct($cms);
        $this->lang                = $cms->getLang();
        $this->inputTransformers   = $inputTransformers;
        $this->resultHandlers      = $resultHandlers;
        $this->exceptionHandlers   = $exceptionHandlers;
        $this->actionSafetyChecker = $actionSafetyChecker;
        $this->actionFormRenderer  = $actionFormRenderer;
        $this->actionButtonBuilder = $actionButtonBuilder;
    }

    public function showForm(ModuleContext $moduleContext, string $actionName, string $objectId = null)
    {
        $module = $moduleContext->getModule();

        $action = $this->loadAction($module, $actionName);

        if (!($action instanceof IParameterizedAction)) {
            abort(404);
        }

        $hiddenValues = [];

        if ($objectId && $action instanceof IObjectAction) {
            /** @var IReadModule $module */
            $object = $this->loadObject($objectId, $action);

            $action = $action->withSubmittedFirstStage([
                IObjectAction::OBJECT_FIELD_NAME => $object,
            ]);

            $hiddenValues[IObjectAction::OBJECT_FIELD_NAME] = $objectId;
            $objectLabel                                    = $module->getLabelFor($object);
            $actionButtons                                  = $this->actionButtonBuilder->buildActionButtons($moduleContext, $object, $actionName);
            $initialStageNumber                             = 2;
        } else {
            $object             = null;
            $objectLabel        = null;
            $actionButtons      = [];
            $initialStageNumber = 1;
        }

        return view('dms::package.module.action')
            ->with([
                'assetGroups'       => ['forms'],
                'pageTitle'         => implode(' :: ', array_merge($moduleContext->getTitles(), [StringHumanizer::title($actionName)])),
                'breadcrumbs'       => $moduleContext->getBreadcrumbs(),
                'finalBreadcrumb'   => StringHumanizer::title($actionName),
                'objectLabel'       => $objectLabel ? str_singular(StringHumanizer::title($module->getName())) . ': ' . $objectLabel : null,
                'actionButtons'     => $actionButtons,
                'objectId'          => $objectId,
                'actionFormContent' => $this->actionFormRenderer->renderActionForm($moduleContext, $action, $hiddenValues, $object, $initialStageNumber),
            ]);
    }

    protected function loadFormStage(Request $request, ModuleContext $moduleContext, string $actionName, int $stageNumber, string $objectId = null, &$object = null) : IForm
    {
        $action = $this->loadAction($moduleContext->getModule(), $actionName);

        if (!($action instanceof IParameterizedAction)) {
            throw new HttpResponseException(response()->json([
                'message' => 'This action does not require an input form',
            ], 403));
        }

        if ($objectId && $action instanceof IObjectAction) {
            $object = $this->loadObject($objectId, $action);

            $action = $action->withSubmittedFirstStage([
                IObjectAction::OBJECT_FIELD_NAME => $object,
            ]);

            $stageNumber--;
        }

        $form        = $action->getStagedForm();
        $stageNumber = (int)$stageNumber;

        if ($stageNumber < 1 || $stageNumber > $form->getAmountOfStages()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Invalid stage number',
            ], 404));
        }

        $input = $this->inputTransformers->transform($moduleContext, $action, $request->all());
        return $form->getFormForStage($stageNumber, $input);
    }

    public function getFormStage(Request $request, ModuleContext $moduleContext, string $actionName, int $stageNumber, string $objectId = null)
    {
        if (!$objectId) {
            $objectId = $request->has(IObjectAction::OBJECT_FIELD_NAME) ? (int)$request->input(IObjectAction::OBJECT_FIELD_NAME) : null;
        }

        $module = $moduleContext->getModule();
        $action = $this->loadAction($module, $actionName);

        try {
            $form = $this->loadFormStage($request, $moduleContext, $actionName, $stageNumber, $objectId, $object);
        } catch (\Exception $e) {
            return $this->exceptionHandlers->handle($moduleContext, $action, $e);
        }

        $renderingContext = new FormRenderingContext($moduleContext, $action, $stageNumber, $object);
        return response($this->actionFormRenderer->renderFormFields($renderingContext, $form), 200);
    }

    public function showActionResult(Request $request, ModuleContext $moduleContext, string $actionName, string $objectId = null)
    {
        $module = $moduleContext->getModule();
        $action = $this->loadAction($module, $actionName);

        if (!$this->actionSafetyChecker->isSafeToShowActionResultViaGetRequest($action)) {
            abort(404);
        }

        try {
            $result = $this->runActionWithDataFromRequest($request, $moduleContext, $action, [IObjectAction::OBJECT_FIELD_NAME => $objectId]);
        } catch (InvalidFormSubmissionException $e) {
            abort(404);
        }

        $response = $this->resultHandlers->handle($moduleContext, $action, $result);

        if ($objectId && $module instanceof IReadModule) {
            /** @var IReadModule $module */
            $object        = $module->getDataSource()->get($objectId);
            $objectLabel   = $module->getLabelFor($object);
            $actionButtons = $this->actionButtonBuilder->buildActionButtons($moduleContext, $object, $actionName);
        } else {
            $objectLabel   = null;
            $actionButtons = [];
        }

        return view('dms::package.module.details')
            ->with([
                'assetGroups'     => ['forms'],
                'pageTitle'       => implode(' :: ', array_merge($moduleContext->getTitles(), [StringHumanizer::humanize($actionName)])),
                'breadcrumbs'     => $moduleContext->getBreadcrumbs(),
                'finalBreadcrumb' => StringHumanizer::title($actionName),
                'objectLabel'     => $objectLabel ? str_singular(StringHumanizer::title($module->getName())) . ': ' . $objectLabel : null,
                'action'          => $action,
                'actionResult'    => $response,
                'actionButtons'   => $actionButtons,
                'objectId'        => $objectId,
            ]);
    }

    public function runFieldRendererActionWithObject(Request $request, ModuleContext $moduleContext, string $actionName, string $objectId, int $stageNumber, string $fieldName, string $fieldRendererAction = null)
    {
        return $this->runFieldRendererAction($request, $moduleContext, $actionName, $stageNumber, $fieldName, $fieldRendererAction, $objectId);
    }

    public function runFieldRendererAction(Request $request, ModuleContext $moduleContext, string $actionName, int $stageNumber, string $fieldName, string $fieldRendererAction = null, string $objectId = null)
    {
        $action = $this->loadAction($moduleContext->getModule(), $actionName);
        $form   = $this->loadFormStage($request, $moduleContext, $actionName, $stageNumber, $objectId, $object);

        if (!$form->hasField($fieldName)) {
            abort(404);
        }

        $renderingContext = new FormRenderingContext($moduleContext, $action, $stageNumber, $object);
        $field            = $form->getField($fieldName);
        $renderer         = $this->actionFormRenderer->getFormRenderer()->getFieldRenderers()->findRendererFor($renderingContext, $field);

        if (!($renderer instanceof IFieldRendererWithActions)) {
            abort(404);
        }

        return $renderer->handleAction($renderingContext, $field, $request, $fieldRendererAction, $request->get('__field_action_data') ?? []);
    }

    public function runAction(Request $request, ModuleContext $moduleContext, string $actionName)
    {
        $action = $this->loadAction($moduleContext->getModule(), $actionName);

        try {
            $result = $this->runActionWithDataFromRequest($request, $moduleContext, $action);
        } catch (\Exception $e) {
            return $this->handleActionException($moduleContext, $action, $e);
        }

        try {
            return $this->resultHandlers->handle($moduleContext, $action, $result);
        } catch (UnhandleableActionResultException $e) {
            return $this->handleUnknownHandlerException($e);
        }
    }

    /**
     * @param Request       $request
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param array         $extraData
     *
     * @return mixed
     */
    protected function runActionWithDataFromRequest(Request $request, ModuleContext $moduleContext, IAction $action, array $extraData = [])
    {
        if ($action instanceof IParameterizedAction) {
            /** @var IParameterizedAction $action */
            $input  = $this->inputTransformers->transform($moduleContext, $action, $request->all() + $extraData);
            $result = $action->run($input);
            return $result;
        } else {
            /** @var IUnparameterizedAction $action */
            $result = $action->run();
            return $result;
        }
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param \Exception    $e
     *
     * @return mixed
     * @throws \Exception
     */
    protected function handleActionException(ModuleContext $moduleContext, IAction $action, \Exception $e)
    {
        try {
            return $this->exceptionHandlers->handle($moduleContext, $action, $e);
        } catch (UnhandleableActionExceptionException $e) {
            return $this->handleUnknownHandlerException($e);
        }
    }

    /**
     * @param \Exception $e
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws
     */
    protected function handleUnknownHandlerException(\Exception $e)
    {
        if (app()->isLocal()) {
            throw $e;
        } else {
            return response()->json([
                'message' => 'An internal error occurred',
            ], 500);
        }
    }

    /**
     * @param IModule $module
     * @param string  $actionName
     *
     * @return IAction
     */
    protected function loadAction(IModule $module, string $actionName) : IAction
    {
        try {
            $action = $module->getAction($actionName);

            if (!$action->isAuthorized()) {
                abort(401);
            }

            return $action;
        } catch (ActionNotFoundException $e) {
            $response = response()->json([
                'message' => 'Invalid action name',
            ], 404);
        }

        throw new HttpResponseException($response);
    }

    /**
     * @param string $objectId
     * @param     $action
     *
     * @return mixed
     */
    protected function loadObject(string $objectId, IObjectAction $action) : ITypedObject
    {
        try {
            /** @var ObjectIdType $objectField */
            $objectFieldType = $action->getObjectForm()->getField(IObjectAction::OBJECT_FIELD_NAME)->getType();
            return $objectFieldType->getObjects()->get($objectId);
        } catch (InvalidInputException $e) {
            abort(404);
        }
    }
}