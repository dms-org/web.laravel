<?php

namespace Dms\Web\Laravel\Action;

use Dms\Core\Exception\BaseException;

/**
 * The exception for action exceptions which have no specified handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UnhandleableActionExceptionException extends BaseException
{

}