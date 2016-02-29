<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Core\Auth\UserForbiddenException;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\InvalidInputException;
use Dms\Core\ICms;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Module\ActionNotFoundException;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IModule;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Module\IUnparameterizedAction;
use Dms\Core\Module\ModuleNotFoundException;
use Dms\Core\Package\PackageNotFoundException;
use Dms\Web\Laravel\Action\ActionExceptionHandlerCollection;
use Dms\Web\Laravel\Action\ActionInputTransformerCollection;
use Dms\Web\Laravel\Action\ActionResultHandlerCollection;
use Dms\Web\Laravel\Action\UnhandleableActionExceptionException;
use Dms\Web\Laravel\Action\UnhandleableActionResultException;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Renderer\Action\ObjectActionButtonBuilder;
use Dms\Web\Laravel\Renderer\Form\ActionFormRenderer;
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

    public function showForm($packageName, $moduleName, $actionName, $objectId = null)
    {
        /** @var IModule $module */
        /** @var IAction $action */
        list($module, $action) = $this->loadAction($packageName, $moduleName, $actionName);
        $titleParts = [$packageName, $moduleName, $actionName];

        if (!($action instanceof IParameterizedAction)) {
            abort(404);
        }

        $hiddenValues = [];

        if ($objectId && $action instanceof IObjectAction) {
            /** @var IReadModule $module */
            /** @var IObjectAction $action */
            try {
                $object = $action->getObjectForm()->getField(IObjectAction::OBJECT_FIELD_NAME)->process($objectId);
            } catch (InvalidInputException $e) {
                abort(404);
            }

            $action = $action->withSubmittedFirstStage([
                IObjectAction::OBJECT_FIELD_NAME => $object,
            ]);

            $hiddenValues[IObjectAction::OBJECT_FIELD_NAME] = $objectId;
            $objectLabel                                    = $module->getLabelFor($object);
            $actionButtons                                  = $this->actionButtonBuilder->buildActionButtons($module, $object, $actionName);
            $initialStageNumber                             = 2;
        } else {
            $objectLabel        = null;
            $actionButtons      = [];
            $initialStageNumber = 1;
        }

        return view('dms::package.module.action')
            ->with([
                'assetGroups'      => ['forms'],
                'pageTitle'          => StringHumanizer::title(implode(' :: ', $titleParts)),
                'breadcrumbs'        => [
                    route('dms::index')                                                 => 'Home',
                    route('dms::package.dashboard', [$packageName])                     => StringHumanizer::title($packageName),
                    route('dms::package.module.dashboard', [$packageName, $moduleName]) => StringHumanizer::title($moduleName),
                ],
                'finalBreadcrumb'    => StringHumanizer::title($actionName),
                'objectLabel'        => $objectLabel ? str_singular(StringHumanizer::title($moduleName)) . ': ' . $objectLabel : null,
                'action'             => $action,
                'formRenderer'       => $this->actionFormRenderer,
                'hiddenValues'       => $hiddenValues,
                'actionButtons'      => $actionButtons,
                'objectId'           => $objectId,
                'initialStageNumber' => $initialStageNumber,
            ]);
    }

    public function getFormStage(Request $request, $packageName, $moduleName, $actionName, $stageNumber)
    {
        /** @var IModule $module */
        /** @var IParameterizedAction $action */
        list($module, $action) = $this->loadAction($packageName, $moduleName, $actionName);

        if (!($action instanceof IParameterizedAction)) {
            return response()->json([
                'message' => 'This action does not require an input form',
            ], 403);
        }

        $form        = $action->getStagedForm();
        $stageNumber = (int)$stageNumber;

        if ($stageNumber < 1 || $stageNumber > $form->getAmountOfStages()) {
            return response()->json([
                'message' => 'Invalid stage number',
            ], 404);
        }

        try {
            if (!$action->isAuthorized()) {
                throw new UserForbiddenException(
                    $this->auth->getAuthenticatedUser(),
                    $action->getRequiredPermissions()
                );
            }

            $input = $this->inputTransformers->transform($action, $request->all());
            $form  = $form->getFormForStage($stageNumber, $input);
        } catch (\Exception $e) {
            return $this->exceptionHandlers->handle($action, $e);
        }

        return response($this->actionFormRenderer->renderFormFields($form), 200);
    }

    public function showActionResult(Request $request, $packageName, $moduleName, $actionName, $objectId = null)
    {
        /** @var IModule $module */
        list($module, $action) = $this->loadAction($packageName, $moduleName, $actionName);

        if (!$this->actionSafetyChecker->isSafeToShowActionResultViaGetRequest($action)) {
            abort(404);
        }

        $titleParts = [$packageName, $moduleName, $actionName];


        try {
            $result = $this->runActionWithDataFromRequest($request, $action, [IObjectAction::OBJECT_FIELD_NAME => $objectId]);
        } catch (InvalidFormSubmissionException $e) {
            abort(404);
        }

        $response = $this->resultHandlers->handle($action, $result);

        if ($objectId && $module instanceof IReadModule) {
            /** @var IReadModule $module */
            $object        = $module->getDataSource()->matching(
                $module->getDataSource()->criteria()->where(Entity::ID, '=', (int)$objectId)
            )[0];
            $objectLabel   = $module->getLabelFor($object);
            $actionButtons = $this->actionButtonBuilder->buildActionButtons($module, $object, $actionName);
        } else {
            $objectLabel   = null;
            $actionButtons = [];
        }

        return view('dms::package.module.details')
            ->with([
                'assetGroups'      => ['forms'],
                'pageTitle'       => StringHumanizer::title(implode(' :: ', $titleParts)),
                'breadcrumbs'     => [
                    route('dms::index')                                                 => 'Home',
                    route('dms::package.dashboard', [$packageName])                     => StringHumanizer::title($packageName),
                    route('dms::package.module.dashboard', [$packageName, $moduleName]) => StringHumanizer::title($moduleName),
                ],
                'finalBreadcrumb' => StringHumanizer::title($actionName),
                'objectLabel'     => $objectLabel ? str_singular(StringHumanizer::title($moduleName)) . ': ' . $objectLabel : null,
                'action'          => $action,
                'actionResult'    => $response,
                'actionButtons'   => $actionButtons,
                'objectId'        => $objectId,
            ]);
    }

    public function runAction(Request $request, $packageName, $moduleName, $actionName)
    {
        /** @var IModule $module */
        /** @var IAction $action */
        list($module, $action) = $this->loadAction($packageName, $moduleName, $actionName);

        try {
            $result = $this->runActionWithDataFromRequest($request, $action);
        } catch (\Exception $e) {
            return $this->handleActionException($action, $e);
        }

        try {
            return $this->resultHandlers->handle($action, $result);
        } catch (UnhandleableActionResultException $e) {
            return $this->handleUnknownHandlerException($e);
        }
    }

    /**
     * @param Request $request
     * @param IAction $action
     * @param array   $extraData
     *
     * @return mixed
     */
    protected function runActionWithDataFromRequest(Request $request, IAction $action, array $extraData = [])
    {
        if ($action instanceof IParameterizedAction) {
            /** @var IParameterizedAction $action */
            $input  = $this->inputTransformers->transform($action, $request->all() + $extraData);
            $result = $action->run($input);
            return $result;
        } else {
            /** @var IUnparameterizedAction $action */
            $result = $action->run();
            return $result;
        }
    }

    /**
     * @param IAction    $action
     * @param \Exception $e
     *
     * @return mixed
     */
    protected function handleActionException(IAction $action, \Exception $e)
    {
        try {
            return $this->exceptionHandlers->handle($action, $e);
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
     * @param string $packageName
     * @param string $moduleName
     * @param string $actionName
     *
     * @return array
     */
    protected function loadAction(string $packageName, string $moduleName, string $actionName) : array
    {
        try {
            $module = $this->cms->loadPackage($packageName)->loadModule($moduleName);
            $action = $module->getAction($actionName);

            if (!$action->isAuthorized()) {
                abort(401);
            }

            return [$module, $action];
        } catch (PackageNotFoundException $e) {
            $response = response()->json([
                'message' => 'Invalid package name',
            ], 404);
        } catch (ModuleNotFoundException $e) {
            $response = response()->json([
                'message' => 'Invalid module name',
            ], 404);
        } catch (ActionNotFoundException $e) {
            $response = response()->json([
                'message' => 'Invalid action name',
            ], 404);
        }

        throw new HttpResponseException($response);
    }
}