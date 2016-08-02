<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ExceptionHandler;

use Dms\Core\Language\ErrorMessageException;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionExceptionHandler;
use Dms\Web\Laravel\Http\ModuleContext;
use Illuminate\Http\Response;

/**
 * The error message exception handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ErrorMessageExceptionHandler extends ActionExceptionHandler
{
    /**
     * @var ILanguageProvider
     */
    protected $langProvider;

    /**
     * ErrorMessageExceptionHandler constructor.
     *
     * @param ILanguageProvider $langProvider
     */
    public function __construct(ILanguageProvider $langProvider)
    {
        parent::__construct();
        $this->langProvider = $langProvider;
    }


    /**
     * @return string|null
     */
    protected function supportedExceptionType()
    {
        return ErrorMessageException::class;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param \Exception    $exception
     *
     * @return bool
     */
    protected function canHandleException(ModuleContext $moduleContext, IAction $action, \Exception $exception) : bool
    {
        return true;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param \Exception    $exception
     *
     * @return Response|mixed
     */
    protected function handleException(ModuleContext $moduleContext, IAction $action, \Exception $exception)
    {
        /** @var ErrorMessageException $exception */
        return \response()->json([
            'message'      => $this->langProvider->format($exception->getLangMessage()),
            'message_type' => 'danger',
        ], 500);
    }
}