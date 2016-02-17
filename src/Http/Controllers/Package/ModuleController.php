<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Core\ICms;
use Dms\Core\Package\IPackage;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Renderer\Module\ModuleRendererCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

    /**
     * @var ModuleRendererCollection
     */
    protected $moduleRenderers;

    /**
     * ModuleController constructor.
     *
     * @param ICms                     $cms
     * @param ModuleRendererCollection $moduleRenderers
     */
    public function __construct(ICms $cms, ModuleRendererCollection $moduleRenderers)
    {
        parent::__construct($cms);
        $this->moduleRenderers = $moduleRenderers;
    }

    /**
     * @param Request $request
     * @param string  $packageName
     * @param string  $moduleName
     *
     * @return Response
     */
    public function index(Request $request, string $packageName, string $moduleName) : Response
    {
        $this->loadModuleAndPackage($packageName, $moduleName);

        return view('dms::package.module.dashboard')
            ->with([
                'pageTitle'       => ucwords($packageName . ' > ' . $moduleName),
                'breadcrumbs'     => [
                    route('dms::index')                           => 'Home',
                    route('dms::package.dashboard', $packageName) => ucwords($packageName),
                ],
                'moduleRenderers' => $this->moduleRenderers,
                'module'          => $this->module,
            ]);
    }

    protected function loadModuleAndPackage($packageName, $moduleName)
    {
        if (!$this->cms->hasPackage($packageName)) {
            abort(404, 'Unrecognized package name');
        }

        $this->package = $this->cms->loadPackage($packageName);

        if (!$this->package->hasModule($moduleName)) {
            abort(404, 'Unrecognized module name');
        }

        $this->module = $this->package->loadModule($moduleName);
    }
}