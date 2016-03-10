<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\ICms;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Renderer\Form\ActionFormRenderer;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

/**
 * The user controller
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UserController extends DmsController
{
    /**
     * @var ModuleContext
     */
    protected $moduleContext;

    public function __construct(ICms $cms)
    {
        parent::__construct($cms);
    }


    public function showProfileForm()
    {
        $user = $this->auth->getAuthenticatedUser();

        return view('dms::auth.profile', ['user' => $user]);
    }

    public function updateUserProfile(Request $request)
    {
        $this->validate($request, [
            'username' => ''
        ]);

    }

    public function showChangePasswordForm()
    {

    }

    public function updateUserPassword()
    {

    }
}
