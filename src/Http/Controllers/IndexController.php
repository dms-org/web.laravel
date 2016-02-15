<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Http\Controllers;

/**
 * The root controller.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IndexController extends DmsController
{
    public function index()
    {
        $packageNames = $this->cms->getPackageNames();
        $package = null;

        foreach ($packageNames as $name) {
            $package = $this->cms->loadPackage($name);
            if ($package->hasDashboard()) {
                break;
            }
        }

        if ($package) {
            return redirect()
                ->route('dms::package.dashboard', ['package' => $package->getName()]);
        }

        $firstPackage = $this->cms->loadPackage(reset($packageNames));
        $modules = $firstPackage->getModuleNames();
        $firstModuleName = reset($modules);

        return redirect()
            ->route('dms::package.module.dashboard', [
                'package' => $firstPackage->getName(),
                'module'  => $firstModuleName,
            ]);
    }
}