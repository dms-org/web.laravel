<?php

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Common\Structure\FileSystem\UploadedFileFactory;
use Dms\Core\Auth\UserForbiddenException;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\ICms;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Module\ActionNotFoundException;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Module\ModuleNotFoundException;
use Dms\Core\Package\PackageNotFoundException;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

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
     * ActionController constructor.
     *
     * @param ICms $cms
     */
    public function __construct(ICms $cms)
    {
        parent::__construct($cms);
        $this->lang = $cms->getLang();
    }

    public function getActionInfo(Request $request, $packageName, $moduleName, $actionName)
    {
        $action = $this->loadAction($packageName, $moduleName, $actionName);

        if ($action instanceof IObjectAction) {
            $type = 'object';
        } elseif ($action instanceof IParameterizedAction) {
            $type = 'parameterized';
        } else {
            $type = 'unparameterized';
        }

        // TODO
    }

    public function getFormStage(Request $request, $packageName, $moduleName, $actionName, $stageNumber)
    {
        $action = $this->loadAction($packageName, $moduleName, $actionName);

        // TODO:
    }

    public function runAction(Request $request, $packageName, $moduleName, $actionName)
    {
        try {
            $action = $this->loadAction($packageName, $moduleName, $actionName);

            // TODO
        } catch (UserForbiddenException $e) {
            return response()->json([
                    'message' => 'User is forbidden from running this action'
            ], 403);
        } catch (InvalidFormSubmissionException $e) {
            return response()->json([
                    'messages' => $this->transformInvalidFormSubmissionToArray($e)
            ], 422);
        }
    }

    /**
     * @param string $packageName
     * @param string $moduleName
     * @param string $actionName
     *
     * @return \Dms\Core\Module\IAction
     */
    protected function loadAction($packageName, $moduleName, $actionName)
    {
        try {
            $action = $this->cms
                    ->loadPackage($packageName)
                    ->loadModule($moduleName)
                    ->getAction($actionName);

            return $action;
        } catch (PackageNotFoundException $e) {
            $response = response()->json([
                    'message' => 'Invalid package name'
            ], 400);
        } catch (ModuleNotFoundException $e) {
            $response = response()->json([
                    'message' => 'Invalid module name'
            ], 400);
        } catch (ActionNotFoundException $e) {
            $response = response()->json([
                    'message' => 'Invalid action name'
            ], 400);
        }

        throw new HttpResponseException($response);
    }

    /**
     * @param array $input
     *
     * @return array
     */
    private function transformInput(array $input)
    {
        foreach ($input as $key => $value) {
            if ($value instanceof SymfonyUploadedFile) {
                $value = UploadedFileFactory::build(
                        $value->getRealPath(),
                        $value->getError(),
                        $value->getClientOriginalName(),
                        $value->getClientMimeType()
                );
            }

            $input[$key] = $value;
        }

        return $input;
    }

    /**
     * @param InvalidFormSubmissionException $exception
     *
     * @return array
     */
    private function transformInvalidFormSubmissionToArray(InvalidFormSubmissionException $exception)
    {
        $validation = [
                'fields'      => [],
                'constraints' => [],
        ];

        foreach ($exception->getFieldMessageMap() as $field => $messages) {
            $validation['fields'][$field] = $this->lang->formatAll($messages);
        }

        foreach ($exception->getInvalidInnerFormSubmissionExceptions() as $field => $innerException) {
            $validation['fields'][$field] = $this->transformInvalidFormSubmissionToArray($innerException);
        }

        $validation['constraints'] = $this->lang->formatAll($exception->getAllConstraintMessages());

        return $validation;
    }
}