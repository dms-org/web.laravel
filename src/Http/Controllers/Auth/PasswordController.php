<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Http\Controllers\Auth;

use Dms\Core\Auth\IUser;
use Dms\Core\ICms;
use Dms\Web\Laravel\Auth\Password\IPasswordResetService;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;

/**
 * The password reset controller
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PasswordController extends DmsController
{
    /**
     * @var PasswordBroker
     */
    protected $passwordBroker;

    /**
     * @var IPasswordResetService
     */
    protected $passwordResetService;

    /**
     * Create a new password controller instance.
     *
     * @param ICms                  $cms
     * @param PasswordBroker        $passwordBroker
     * @param IPasswordResetService $passwordResetService
     */
    public function __construct(ICms $cms, PasswordBroker $passwordBroker, IPasswordResetService $passwordResetService)
    {
        parent::__construct($cms);

        $this->middleware('dms.guest');
        $this->passwordBroker = $passwordBroker;
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showResetLinkEmailForm()
    {
        return view('dms::auth.password.forgot');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $response = $this->passwordBroker->sendResetLink($request->only('email'), function (Message $message) {
            $message->subject('Your Password Reset Link');
        });

        switch ($response) {
            case PasswordBroker::RESET_LINK_SENT:
                return redirect()->back()->with('status', trans($response));

            case PasswordBroker::INVALID_USER:
                return redirect()->back()->withErrors(['email' => trans($response)]);
        }
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  string|null $token
     *
     * @return \Illuminate\Http\Response
     */
    public function showPasswordResetForm(string $token = null)
    {
        if (!$token) {
            return $this->showResetLinkEmailForm();
        }

        return view('dms::auth.password.reset')->with('token', $token);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $this->validate($request, [
            'token'    => 'required',
            'username' => 'required',
            'password' => 'required|confirmed|min:6|max:50',
        ]);

        $credentials = $request->only(
            'username', 'password', 'password_confirmation', 'token'
        );

        $response = $this->passwordBroker->reset($credentials, function (IUser $user, $password) {
            $this->passwordResetService->resetUserPassword($user, $password);
        });

        switch ($response) {
            case PasswordBroker::PASSWORD_RESET:
                return redirect()->route('dms::auth.login')->with('status', trans($response));

            default:
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => trans($response)]);
        }
    }
}
