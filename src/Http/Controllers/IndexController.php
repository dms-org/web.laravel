<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers;

use Dms\Web\Laravel\Renderer\Package\PackageRendererCollection;

/**
 * The root controller.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IndexController extends DmsController
{
    public function index(PackageRendererCollection $dashboardRenderer)
    {
        if ($this->cms->hasPackage('analytics')) {
            $package           = $this->cms->loadPackage('analytics');
            $analyticsWidgets = $dashboardRenderer->findRendererFor($package)->render($package);
        } else {
            $analyticsWidgets = null;
        }

        return view('dms::dashboard')
            ->with([
                'assetGroups'      => ['tables', 'charts'],
                'title'            => 'Dashboard',
                'pageTitle'        => 'Dashboard',
                'finalBreadcrumb'  => 'Dashboard',
                'analyticsWidgets' => $analyticsWidgets,
            ]);
    }
}