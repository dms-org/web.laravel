<?php

namespace Dms\Web\Laravel\Http\Controllers;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\ICms;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * The base dms controller.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DmsController extends Controller
{
    use ValidatesRequests;

    /**
     * @var ICms
     */
    protected $cms;

    /**
     * @var IAuthSystem
     */
    protected $auth;

    /**
     * DmsController constructor.
     *
     * @param ICms $cms
     */
    public function __construct(ICms $cms)
    {
        $this->cms = $cms;
        $this->auth = app()->make(IAuthSystem::class);

        $this->loadSharedViewVariables(request());
    }

    /**
     * @param Request $request
     */
    private function loadSharedViewVariables(Request $request)
    {
        view()->share([
            'cms'   => $this->cms,
            'user'  => $this->auth->isAuthenticated() ? $this->auth->getAuthenticatedUser() : null,
            'title' => 'DMS {' . $request->server->get('SERVER_NAME') . '}',
        ]);
    }
}