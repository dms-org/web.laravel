<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Action;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IAction;
use Illuminate\Http\Response;

/**
 * The action result handler collection class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionResultHandlerCollection
{
    /**
     * @var IActionResultHandler[][]
     */
    protected $handlers;

    /**
     * ActionResultHandlerCollection constructor.
     *
     * @param IActionResultHandler[] $handlers
     */
    public function __construct(array $handlers)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'handlers', $handlers, IActionResultHandler::class);

        foreach ($handlers as $handler) {
            $this->handlers[$handler->getSupportedResultType()][] = $handler;
        }
    }

    /**
     * Handles the supplied action result.
     *
     * @param IAction $action
     * @param mixed   $result
     *
     * @return Response|mixed
     * @throws UnhandleableActionResultException
     */
    public function handle(IAction $action, $result)
    {
        return $this->findHandlerFor($action, $result)->handle($action, $result);
    }

    /**
     * @param IAction $action
     * @param mixed   $result
     *
     * @return IActionResultHandler
     * @throws UnhandleableActionResultException
     */
    public function findHandlerFor(IAction $action, $result) : IActionResultHandler
    {
        $resultClass = get_class($result);

        while ($resultClass) {

            if (isset($this->handlers[$resultClass])) {
                foreach ($this->handlers[$resultClass] as $resultHandler) {
                    if ($resultHandler->accepts($action, $result)) {
                        return $resultHandler;
                    }
                }
            }

            $resultClass = get_parent_class($resultClass);
        }

        throw UnhandleableActionResultException::format(
            'Could not handle action result of type %s from action \'%s\': no matching action handler could be found',
            get_class($result), $action->getName()
        );
    }
}