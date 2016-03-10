<?php

namespace Dms\Web\Laravel\View;

use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\ICms;
use Dms\Core\Module\IModule;
use Dms\Core\Package\IDashboard;
use Dms\Core\Package\IPackage;
use Dms\Web\Laravel\Auth\LaravelAuthSystem;
use Dms\Web\Laravel\Util\StringHumanizer;
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

        /** @var LaravelAuthSystem $authSystem */
        $authSystem = $this->cms->getAuth();
        $view->with('navigation', $this->filterElementsByPermissions(
            $authSystem->getAuthenticatedUser()->isSuperUser(),
            $this->getPermissionNames($authSystem->getUserPermissions()),
            $this->cache->remember(
                $navigationCacheKey,
                self::NAVIGATION_CACHE_EXPIRY_MINUTES,
                function () {
                    return $this->loadNavigation();
                }
            ))
        );
    }

    private function filterElementsByPermissions(bool $isSuperUser, array $permissionNames, array $navigationElements) : array
    {
        $navigation = [];

        foreach ($navigationElements as $element) {
            if ($element instanceof NavigationElementGroup) {
                $subNavigation = $this->filterElementsByPermissions($isSuperUser, $permissionNames, $element->getElements());

                if ($subNavigation) {
                    $navigation[] = $element->withElements($subNavigation);
                }
            } elseif ($element instanceof NavigationElement) {
                if ($isSuperUser || $element->shouldDisplay($permissionNames)) {
                    $navigation[] = $element;
                }
            }
        }

        return $navigation;
    }

    private function loadNavigation() : array
    {
        $navigation = [];

        $navigation[] = new NavigationElement('Home', route('dms::index'), 'tachometer');

        foreach ($this->cms->loadPackages() as $package) {
            $packageNavigation = [];

            if ($package->hasDashboard()) {
                $packageNavigation[] = new NavigationElement(
                    'Dashboard',
                    route('dms::package.dashboard', [$package->getName()]),
                    'tachometer',
                    $this->getPermissionNames($this->getCommonPermissions($package->loadDashboard()))
                );
            }

            $packageLabel = StringHumanizer::title($package->getName());

            foreach ($package->loadModules() as $module) {
                $moduleDashboardUrl  = route('dms::package.module.dashboard', [$package->getName(), $module->getName()]);
                $moduleLabel         = StringHumanizer::title($module->getName());
                $packageNavigation[] = new NavigationElement(
                    $moduleLabel,
                    $moduleDashboardUrl,
                    $this->getModuleIcon($package, $module),
                    $this->getPermissionNames($module->getRequiredPermissions())
                );
            }

            if (count($packageNavigation) === 1) {
                $navigation[] = $packageNavigation[0];
            } else {
                $navigation[] = new NavigationElementGroup($packageLabel, $this->getPackageIcon($package), $packageNavigation);
            }
        }

        return $navigation;
    }

    /**
     * @param array $permissions
     *
     * @return array
     */
    protected function getPermissionNames(array $permissions) : array
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'permissions', $permissions, IPermission::class);
        $names = [];

        foreach ($permissions as $permission) {
            $names[] = $permission->getName();
        }

        return $names;
    }

    private function getCommonPermissions(IDashboard $dashboard) : array
    {
        $permissionGroups = [];

        foreach ($dashboard->getWidgets() as $widget) {
            $permissionGroups[] = $widget->getModule()->getRequiredPermissions();
            $permissionGroups[] = $widget->getWidget()->getRequiredPermissions();
        }

        $permissions = array_shift($permissionGroups);

        foreach ($permissionGroups as $permissionGroup) {
            foreach ($permissions as $key => $permission) {
                if (!in_array($permission, $permissionGroup)) {
                    unset($permissions[$key]);
                }
            }
        }

        return $permissions;
    }

    private function getPackageIcon(IPackage $package) : string
    {
        $name = 'dms::icons.packages.' . $package->getName();
        $icon = trans($name);
        return $icon === $name ? 'folder' : $icon;
    }

    private function getModuleIcon(IPackage $package, IModule $module) : string
    {
        $name = 'dms::icons.modules.' . $package->getName() . '.' . $module->getName();
        $icon = trans($name);
        return $icon === $name ? 'circle-o' : $icon;
    }
}