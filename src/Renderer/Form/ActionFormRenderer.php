<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Form\IForm;
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
     * @param array                $hiddenValues
     * @param int                  $initialStageNumber
     *
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderActionForm(IParameterizedAction $action, array $hiddenValues = [], int $initialStageNumber = 1) : string
    {
        return view('dms::components.form.staged-form')
            ->with([
                'action'             => $action,
                'stagedForm'         => $action->getStagedForm(),
                'formRenderer'       => $this->formRenderer,
                'packageName'        => $action->getPackageName(),
                'moduleName'         => $action->getModuleName(),
                'actionName'         => $action->getName(),
                'submitButtonClass'  => $this->keywordTypeIdentifier->getTypeFromName($action->getName()),
                'hiddenValues'       => $hiddenValues,
                'initialStageNumber' => $initialStageNumber,
            ])
            ->render();
    }

    /**
     * Renders the supplied form fields.
     *
     * @param IForm $form
     *
     * @return string
     */
    public function renderFormFields(IForm $form) : string
    {
        return $this->formRenderer->renderFields($form);
    }
}