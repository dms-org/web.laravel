<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Core\ICms;
use Dms\Core\Package\IPackage;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Renderer\Package\PackageRendererCollection;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Request;

/**
 * The packages controller.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PackageController extends DmsController
{
    /**
     * @var IPackage
     */
    protected $package;

    /**
     * @var PackageRendererCollection
     */
    protected $packageRenderers;

    /**
     * PackageController constructor.
     *
     * @param ICms                      $cms
     * @param PackageRendererCollection $packageRenderers
     */
    public function __construct(ICms $cms, PackageRendererCollection $packageRenderers)
    {
        parent::__construct($cms);
        $this->packageRenderers = $packageRenderers;
    }


    public function showDashboard(Request $request)
    {
        $this->loadPackage($request);

        if (!$this->package->hasDashboard()) {
            $moduleNames = $this->package->getModuleNames();
            $firstModule = reset($moduleNames);

            return redirect()
                ->route('dms::package.module.dashboard', [
                    'package' => $this->package->getName(),
                    'module'  => $firstModule,
                ]);
        }

        $packageName = $this->package->getName();

        return view('dms::package.dashboard')
            ->with([
                'pageTitle'        => StringHumanizer::title($packageName) . ' :: Dashboard',
                'breadcrumbs'      => [
                    route('dms::index') => 'Home',
                ],
                'finalBreadcrumb'  => StringHumanizer::title($packageName),
                'packageRenderers' => $this->packageRenderers,
                'package'          => $this->package,
            ]);
    }

    protected function loadPackage(Request $request)
    {
        $packageName = $request->route('package');

        if (!$this->cms->hasPackage($packageName)) {
            abort(404, 'Unrecognized package name');
        }

        $this->package = $this->cms->loadPackage($packageName);
    }
}