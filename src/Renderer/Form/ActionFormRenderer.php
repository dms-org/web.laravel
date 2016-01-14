<?php

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Module\IParameterizedAction;
use Dms\Web\Laravel\Util\KeywordTypeIdentifier;

/**
 * The action form renderer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionFormRenderer
{
    /**
     * @var FormRenderer
     */
    protected $formRenderer;

    /**
     * @var KeywordTypeIdentifier
     */
    protected $keywordTypeIdentifier;

    /**
     * ActionFormRenderer constructor.
     *
     * @param FormRenderer          $formRenderer
     * @param KeywordTypeIdentifier $keywordTypeIdentifier
     */
    public function __construct(FormRenderer $formRenderer, KeywordTypeIdentifier $keywordTypeIdentifier)
    {
        $this->formRenderer          = $formRenderer;
        $this->keywordTypeIdentifier = $keywordTypeIdentifier;
    }

    /**
     * Renders the action form as a staged form.
     *
     * @param IParameterizedAction $action
     *
     * @return string
     */
    public function renderActionForm(IParameterizedAction $action)
    {
        return (string)view('dms::components.form.staged-form')
                ->with([
                        'action'            => $action,
                        'stagedForm'        => $action->getStagedForm(),
                        'formRenderer'      => $this->formRenderer,
                        'packageName'       => $action->getPackageName(),
                        'moduleName'        => $action->getModuleName(),
                        'actionName'        => $action->getName(),
                        'submitButtonClass' => $this->keywordTypeIdentifier->getTypeFromName($action->getName())
                ]);
    }
}