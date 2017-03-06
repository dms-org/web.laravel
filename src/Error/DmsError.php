<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Error;

use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * The dms error pages.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DmsError
{
    /**
     * @param int    $statusCode
     * @param string $message
     *
     * @return void
     */
    public static function abort(int $statusCode, string $message = '')
    {
        if (request()->ajax()) {
            throw new HttpResponseException(response($message, $statusCode));
        }

        throw new HttpResponseException(response(self::renderErrorView($statusCode), $statusCode));
    }

    /**
     * @param int $statusCode
     *
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    protected static function renderErrorView(int $statusCode)
    {
        return view('dms::errors.' . $statusCode)
            ->with('title', $statusCode)
            ->with('user', request()->user())
            ->with('pageTitle', $statusCode)
            ->with('finalBreadcrumb', $statusCode)
            ->render();
    }
}