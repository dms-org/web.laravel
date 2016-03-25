<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Http\Middleware;

use Closure;
use Dms\Core\Auth\IAuthSystem;

class Authenticate
{
    /**
     * @var IAuthSystem
     */
    protected $auth;

    /**
     * Authenticate constructor.
     *
     * @param IAuthSystem $auth
     */
    public function __construct(IAuthSystem $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->auth->isAuthenticated()) {
            if ($request->ajax()) {
                return response('Unauthenticated', 401);
            } else {
                return redirect()->guest(route('dms::auth.login'));
            }
        }

        return $next($request);
    }
}
