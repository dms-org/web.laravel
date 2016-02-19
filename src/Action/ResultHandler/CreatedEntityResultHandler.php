<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Common\Crud\Action\Crud\CreateAction;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\Util\EntityModuleMap;
use Illuminate\Http\Response;

/**
 * The created entity action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CreatedEntityResultHandler extends ActionResultHandler
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
        parent::__construct();
        return $action instanceof CreateAction;
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
        $type   = str_singular($module->getName());

        return \response()->json([
            'message'  => "The '{$label}' {$type} has been created.",
            'redirect' => route('dms::package.module.dashboard', [
                $module->getPackageName(),
                $module->getName(),
            ]),
        ]);
    }
}