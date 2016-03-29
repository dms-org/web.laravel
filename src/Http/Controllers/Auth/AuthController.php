<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Auth;

use Dms\Core\Auth\AdminBannedException;
use Dms\Core\Auth\InvalidCredentialsException;
use Dms\Core\Auth\NotAuthenticatedException;
use Dms\Core\ICms;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

/**
 * The login controller.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AuthController extends DmsController
{
    /**
     * Create a new authentication controller instance.
     *
     * @param ICms $cms
     */
    public function __construct(ICms $cms)
    {
        parent::__construct($cms);

        $this->middleware('dms.guest', ['except' => 'logout']);
    }

    /**
     * Get the maximum number of login attempts for delaying further attempts.
     *
     * @return int
     */
    protected function maxLoginAttempts() : int
    {
        return config('dms.auth.login.max-attempts');
    }

    /**
     * The number of seconds to delay further login attempts.
     *
     * @return int
     */
    protected function lockoutTime() : int
    {
        return config('dms.auth.login.lockout-time');
    }

    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('dms::auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        try {
            $this->auth->login($request->input('username'), $request->input('password'));

            $this->clearLoginAttempts($request);

            if ($request->ajax()) {
                return \response()->json([
                    'response'   => 'Authenticated',
                    'csrf_token' => csrf_token(),
                ]);
            } else {
                return redirect()->intended(route('dms::index'));
            }
        } catch (InvalidCredentialsException $e) {
            $errorMessage = 'dms::auth.failed';
        } catch (AdminBannedException $e) {
            $errorMessage = 'dms::auth.banned';
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        if ($request->ajax()) {
            return response('Failed', 400);
        } else {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => trans($errorMessage),
                ]);
        }
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        try {
            $this->auth->logout();
        } catch (NotAuthenticatedException $e) {

        }

        return redirect('/');
    }

    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function hasTooManyLoginAttempts(Request $request) : bool
    {
        return app(RateLimiter::class)->tooManyAttempts(
            $this->getThrottleKey($request),
            $this->maxLoginAttempts(), $this->lockoutTime() / 60
        );
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function incrementLoginAttempts(Request $request)
    {
        app(RateLimiter::class)->hit(
            $this->getThrottleKey($request)
        );
    }

    /**
     * Determine how many retries are left for the user.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return int
     */
    protected function retriesLeft(Request $request) : int
    {
        $attempts = app(RateLimiter::class)->attempts(
            $this->getThrottleKey($request)
        );

        return $this->maxLoginAttempts() - $attempts + 1;
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = app(RateLimiter::class)->availableIn(
            $this->getThrottleKey($request)
        );

        return redirect()->back()
            ->withInput($request->only('username', 'remember'))
            ->withErrors([
                'username' => trans('dms::auth.throttle', ['seconds' => $seconds]),
            ]);
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function clearLoginAttempts(Request $request)
    {
        app(RateLimiter::class)->clear(
            $this->getThrottleKey($request)
        );
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function getThrottleKey(Request $request) : string
    {
        return mb_strtolower($request->input('username')) . '|' . $request->ip();
    }
}
