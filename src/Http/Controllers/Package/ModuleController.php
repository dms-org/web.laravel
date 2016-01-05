<?php

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Core\ICms;
use Dms\Core\Package\IPackage;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Illuminate\Http\Request;

/**
 * The module controller.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleController extends DmsController
{
    /**
     * @var IPackage
     */
    protected $package;

    /**
     * @var IPackage
     */
    protected $module;

    public function index(Request $request)
    {
        $this->loadModuleAndPackage($request);

    }

    public function widget(Request $request, $name)
    {
        $this->loadModuleAndPackage($request);
        // TODO:
    }

    protected function loadModuleAndPackage(Request $request)
    {
        $packageName = $request->route('package');

        if (!$this->cms->hasPackage($packageName)) {
            abort(404, 'Unrecognized package name');
        }

        $this->package = $this->cms->loadPackage($packageName);

        $moduleName = $request->route('module');

        if (!$this->package->hasModule($moduleName)) {
            abort(404, 'Unrecognized module name');
        }

        $this->module = $this->package->loadModule($moduleName);
    }
}