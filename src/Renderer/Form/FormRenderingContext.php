<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IForm;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Module\IParameterizedAction;
use Dms\Web\Laravel\Http\ModuleContext;

/**
 * The form rendering context.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormRenderingContext
{
    /**
     * @var ModuleContext
     */
    protected $moduleContext;

    /**
     * @var IParameterizedAction|null
     */
    protected $currentAction;

    /**
     * @var ITypedObject|null
     */
    protected $object;

    /**
     * @var int|null
     */
    protected $objectId;

    /**
     * @var int|null
     */
    protected $currentStageNumber;

    /**
     * @var IForm|null
     */
    protected $currentForm;

    /**
     * @var IParameterizedAction
     */
    private $action;

    /**
     * FormRenderingContext constructor.
     *
     * @param ModuleContext        $moduleContext
     * @param IParameterizedAction $action
     * @param int                  $currentStageNumber
     * @param ITypedObject         $object
     */
    public function __construct(ModuleContext $moduleContext, IParameterizedAction $action = null, int $currentStageNumber = null, ITypedObject $object = null)
    {
        $this->moduleContext      = $moduleContext;
        $this->action             = $action;
        $this->currentStageNumber = $currentStageNumber;
        $this->setObject($object);
    }

    /**
     * @return ModuleContext
     */
    public function getModuleContext()
    {
        return $this->moduleContext;
    }

    /**
     * @return IParameterizedAction
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return IParameterizedAction|null
     */
    public function getCurrentAction()
    {
        return $this->currentAction;
    }

    /**
     * @return int|null
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @return ITypedObject|null
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param ITypedObject $object
     *
     * @throws InvalidArgumentException
     * @internal param int|null $objectId
     */
    public function setObject(ITypedObject $object = null)
    {
        if (!$object) {
            $this->object = null;
            $this->objectId = null;

            return;
        }

        $module = $this->moduleContext->getModule();
        if (!($module instanceof IReadModule)) {
            throw InvalidArgumentException::format('Module must be an instance of %s', IReadModule::class);
        }

        $this->object = $object;
        $this->objectId = $module->getDataSource()->getObjectId($object);
    }

    /**
     * @param IParameterizedAction|null $currentAction
     */
    public function setCurrentAction(IParameterizedAction $currentAction = null)
    {
        $this->currentAction = $currentAction;
    }

    /**
     * @return int|null
     */
    public function getCurrentStageNumber()
    {
        return $this->currentStageNumber;
    }

    /**
     * @param int|null $currentStageNumber
     */
    public function setCurrentStageNumber(int $currentStageNumber = null)
    {
        $this->currentStageNumber = $currentStageNumber;
    }

    /**
     * @return IForm|null
     */
    public function getCurrentForm()
    {
        return $this->currentForm;
    }

    /**
     * @param IForm|null $currentForm
     */
    public function setCurrentForm(IForm $currentForm = null)
    {
        $this->currentForm = $currentForm;
    }
}