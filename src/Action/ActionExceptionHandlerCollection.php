<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IAction;
use Illuminate\Http\Response;

/**
 * The action exception handler collection class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionExceptionHandlerCollection
{
    /**
     * @var IActionExceptionHandler[][]
     */
    protected $handlers;

    /**
     * ActionExceptionHandlerCollection constructor.
     *
     * @param IActionExceptionHandler[] $handlers
     */
    public function __construct(array $handlers)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'handlers', $handlers, IActionExceptionHandler::class);

        foreach ($handlers as $handler) {
            $this->handlers[$handler->getSupportedExceptionType()][] = $handler;
        }
    }

    /**
     * Handles the supplied action exception.
     *
     * @param IAction    $action
     * @param \Exception $exception
     *
     * @return Response|mixed
     * @throws UnhandleableActionExceptionException
     */
    public function handle(IAction $action, \Exception $exception)
    {
        return $this->findHandlerFor($action, $exception)->handle($action, $exception);
    }

    /**
     * @param IAction    $action
     * @param \Exception $exception
     *
     * @return IActionExceptionHandler
     * @throws UnhandleableActionExceptionException
     */
    public function findHandlerFor(IAction $action, \Exception $exception) : IActionExceptionHandler
    {
        $exceptionClass = get_class($exception);

        while ($exceptionClass) {

            if (isset($this->handlers[$exceptionClass])) {
                foreach ($this->handlers[$exceptionClass] as $exceptionHandler) {
                    if ($exceptionHandler->accepts($action, $exception)) {
                        return $exceptionHandler;
                    }
                }
            }

            $exceptionClass = get_parent_class($exceptionClass);
        }

        throw new UnhandleableActionExceptionException(
            sprintf(
                'Could not handle action exception of type %s from action \'%s\': no matching action handler could be found',
                get_class($exception), $action->getName()
            ),
            0,
            $exception
        );
    }
}