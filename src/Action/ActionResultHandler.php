<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Action;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IAction;
use Dms\Core\Util\Debug;
use Illuminate\Http\Response;

/**
 * The action result handler vase class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ActionResultHandler implements IActionResultHandler
{
    /**
     * @var string|null
     */
    protected $supportedResultType;

    /**
     * ActionResultHandler constructor.
     */
    public function __construct()
    {
        $this->supportedResultType = $this->supportedResultType();
    }

    /**
     * @return string|null
     */
    abstract protected function supportedResultType();

    /**
     * @param IAction $action
     * @param mixed   $result
     *
     * @return bool
     */
    abstract protected function canHandleResult(IAction $action, $result) : bool;

    /**
     * @param IAction $action
     * @param mixed   $result
     *
     * @return Response|mixed
     */
    abstract protected function handleResult(IAction $action, $result);

    /**
     * @inheritdoc
     */
    final public function getSupportedResultType()
    {
        return $this->supportedResultType;
    }

    /**
     * @inheritdoc
     */
    final public function accepts(IAction $action, $result) : bool
    {
        if ($this->supportedResultType && !($result instanceof $this->supportedResultType)) {
            return false;
        }

        return $this->canHandleResult($action, $result);
    }

    /**
     * @inheritdoc
     */
    final public function handle(IAction $action, $result)
    {
        if (!$this->canHandleResult($action, $result)) {
            throw InvalidArgumentException::format(
                'Invalid call to %s: action and result of type %s not supported',
                get_class($this) . '::' . __FUNCTION__, Debug::getType($result)
            );
        }

        return $this->handleResult($action, $result);
    }
}