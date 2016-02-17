<?php

namespace Dms\Web\Laravel\View;

use Dms\Core\ICms;
use Illuminate\Cache\Repository as Cache;
use Illuminate\View\View;

/**
 * The dms navigation view composer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DmsNavigationViewComposer
{
    const NAVIGATION_CACHE_EXPIRY_MINUTES = 60;

    /**
     * @var ICms
     */
    protected $cms;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * DmsNavigationViewComposer constructor.
     *
     * @param ICms  $cms
     * @param Cache $cache
     */
    public function __construct(ICms $cms, Cache $cache)
    {
        $this->cms   = $cms;
        $this->cache = $cache;
    }

    /**
     * Bind data to the view.
     *
     * @param  View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $installedModulesHash = md5(implode('__', $this->cms->getPackageNames()));
        $navigationCacheKey   = 'dms:navigation:' . $installedModulesHash;

        $view->with('navigation', $this->cache->remember(
            $navigationCacheKey,
            self::NAVIGATION_CACHE_EXPIRY_MINUTES,
            function () {
                return $this->loadNavigation();
            }));
    }

    private function loadNavigation() : array
    {
        $navigation = [
            route('dms::index') => 'Home',
        ];

        foreach ($this->cms->loadPackages() as $package) {
            $packageNavigation = [
                route('dms::package.dashboard', [$package->getName()]) => 'Dashboard',
            ];

            $packageLabel = ucwords(str_replace('-', ' ', $package->getName()));

            foreach ($package->loadModules() as $module) {
                $moduleDashboardUrl                     = route('dms::package.module.dashboard', [$package->getName(), $module->getName()]);
                $moduleLabel                            = ucwords(str_replace('-', ' ', $package->getName()));
                $packageNavigation[$moduleDashboardUrl] = $moduleLabel;
            }

            $navigation[$packageLabel] = $packageNavigation;
        }

        return $navigation;
    }
}