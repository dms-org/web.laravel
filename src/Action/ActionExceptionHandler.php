<?php

namespace Dms\Web\Laravel\Action;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IAction;
use Dms\Core\Util\Debug;
use Illuminate\Http\Response;

/**
 * The action exception handler base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ActionExceptionHandler implements IActionExceptionHandler
{
    /**
     * @var string|null
     */
    protected $supportedExceptionType;

    /**
     * ActionExceptionHandler constructor.
     */
    public function __construct()
    {
        $this->supportedExceptionType = $this->supportedExceptionType();
    }

    /**
     * @return string|null
     */
    abstract protected function supportedExceptionType();

    /**
     * @param IAction    $action
     * @param \Exception $exception
     *
     * @return bool
     */
    abstract protected function canHandleException(IAction $action, \Exception $exception);

    /**
     * @param IAction    $action
     * @param \Exception $exception
     *
     * @return Response|mixed
     */
    abstract protected function handleException(IAction $action, \Exception $exception);

    /**
     * @inheritdoc
     */
    final public function getSupportedExceptionType()
    {
        return $this->supportedExceptionType;
    }

    /**
     * @inheritdoc
     */
    final public function accepts(IAction $action, \Exception $exception)
    {
        if ($this->supportedExceptionType && !($exception instanceof $this->supportedExceptionType)) {
            return false;
        }

        return $this->canHandleException($action, $exception);
    }

    /**
     * @inheritdoc
     */
    final public function handle(IAction $action, \Exception $exception)
    {
        if (!$this->canHandleException($action, $exception)) {
            throw InvalidArgumentException::format(
                'Invalid call to %s: action and exception of type not supported',
                get_class($this) . '::' . __FUNCTION__, Debug::getType($exception)
            );
        }

        return $this->handleException($action, $exception);
    }
}