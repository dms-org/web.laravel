<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Core\Package\IPackage;
use Dms\Web\Laravel\Http\Controllers\DmsController;
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