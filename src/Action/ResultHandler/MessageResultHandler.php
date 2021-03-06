<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Language\Message;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\Http\ModuleContext;
use Illuminate\Http\Response;

/**
 * The message action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MessageResultHandler extends ActionResultHandler
{
    /**
     * @var ILanguageProvider
     */
    protected $lang;

    /**
     * MessageResultHandler constructor.
     *
     * @param ILanguageProvider $lang
     */
    public function __construct(ILanguageProvider $lang)
    {
        parent::__construct();
        $this->lang = $lang;
    }


    /**
     * @return string|null
     */
    protected function supportedResultType()
    {
        return Message::class;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param mixed         $result
     *
     * @return bool
     */
    protected function canHandleResult(ModuleContext $moduleContext, IAction $action, $result) : bool
    {
        return true;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param mixed         $result
     *
     * @return Response|mixed
     */
    protected function handleResult(ModuleContext $moduleContext, IAction $action, $result)
    {
        /** @var Message $result */
        return \response()->json([
            'message' => $this->lang->format($result),
        ]);
    }
}