<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package\Module;

use Dms\Core\ICms;
use Dms\Core\Module\IModule;
use Dms\Core\Package\IPackage;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Renderer\Module\ModuleRendererCollection;
use Dms\Web\Laravel\Util\StringHumanizer;
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
     * @param IModule $module
     *
     * @return mixed
     */
    public function showDashboard(Request $request, IModule $module)
    {
        $packageName = $module->getPackageName();
        $moduleName  = $module->getName();

        return view('dms::package.module.dashboard')
            ->with([
                'assetGroups'     => ['tables', 'charts'],
                'pageTitle'       => StringHumanizer::title($packageName . ' :: ' . $moduleName),
                'breadcrumbs'     => [
                    route('dms::index')                           => 'Home',
                    route('dms::package.dashboard', $packageName) => StringHumanizer::title($packageName),
                ],
                'finalBreadcrumb' => StringHumanizer::title($moduleName),
                'moduleRenderers' => $this->moduleRenderers,
                'module'          => $module,
            ]);
    }
}