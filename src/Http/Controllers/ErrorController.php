<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers;

use Dms\Web\Laravel\Error\DmsError;

/**
 * The error controller.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ErrorController extends DmsController
{
    public function notFound()
    {
        DmsError::abort(404);
    }
}