<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Common\Crud\Action\Crud\EditAction;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\Util\EntityModuleMap;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Response;

/**
 * The edited entity action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EditedEntityResultHandler extends ActionResultHandler
{
    /**
     * @var EntityModuleMap
     */
    protected $entityModuleMap;

    /**
     * CreatedEntityResultHandler constructor.
     *
     * @param EntityModuleMap $entityModuleMap
     */
    public function __construct(EntityModuleMap $entityModuleMap)
    {
        parent::__construct();
        $this->entityModuleMap = $entityModuleMap;
    }

    /**
     * @return string|null
     */
    protected function supportedResultType()
    {
        return Entity::class;
    }

    /**
     * @param IAction $action
     * @param mixed   $result
     *
     * @return bool
     */
    protected function canHandleResult(IAction $action, $result) : bool
    {
        return $action instanceof EditAction;
    }

    /**
     * @param IAction $action
     * @param mixed   $result
     *
     * @return Response|mixed
     */
    protected function handleResult(IAction $action, $result)
    {
        $module = $this->entityModuleMap->loadModuleFor(get_class($result));
        $label  = $module->getLabelFor($result);
        $type   = str_singular(StringHumanizer::humanize($module->getName()));

        return \response()->json([
            'message' => "The '{$label}' {$type} has been updated.",
        ]);
    }
}