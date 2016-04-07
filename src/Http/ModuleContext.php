<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http;

use Dms\Core\Module\IModule;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;

/**
 * The module context class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleContext
{
    /**
     * @var Router
     */
    protected $moduleRouter;

    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var string
     */
    protected $rootUrl;

    /**
     * @var string[]
     */
    protected $titles = [];

    /**
     * @var string[]
     */
    protected $breadcrumbs = [];

    /**
     * @var callable
     */
    protected $moduleLoaderCallback;

    /**
     * @var IModule|null
     */
    protected $module;

    /**
     * ModuleContext constructor.
     *
     * @param Router           $moduleRouter
     * @param string           $rootUrl
     * @param array            $titles
     * @param \string[]        $breadcrumbs
     * @param IModule|callable $moduleLoaderCallback
     */
    public function __construct(Router $moduleRouter, string $rootUrl, array $titles, array $breadcrumbs, $moduleLoaderCallback)
    {
        $this->moduleRouter = $moduleRouter;
        $this->urlGenerator = new UrlGenerator($moduleRouter->getRoutes(), request());
        $this->rootUrl      = $rootUrl;
        $this->titles       = $titles;
        $this->breadcrumbs  = $breadcrumbs;

        if ($moduleLoaderCallback instanceof IModule) {
            $this->module = $moduleLoaderCallback;
        } else {
            $this->moduleLoaderCallback = $moduleLoaderCallback;
        }
    }

    /**
     * @param Router   $moduleRouter
     * @param string   $packageName
     * @param string   $moduleName
     * @param callable $moduleLoaderCallback
     *
     * @return ModuleContext
     */
    public static function rootContext(Router $moduleRouter, string $packageName, string $moduleName, callable $moduleLoaderCallback) : ModuleContext
    {
        return new ModuleContext(
            $moduleRouter,
            route('dms::package.module.dashboard', [$packageName, $moduleName]),
            [StringHumanizer::title($packageName), StringHumanizer::title($moduleName)],
            [
                route('dms::index')                                                 => 'Home',
                route('dms::package.dashboard', [$packageName])                     => StringHumanizer::title($packageName),
                route('dms::package.module.dashboard', [$packageName, $moduleName]) => StringHumanizer::title($moduleName),
            ],
            $moduleLoaderCallback
        );
    }

    /**
     * @param Router  $moduleRouter
     * @param IModule $module
     *
     * @return ModuleContext
     */
    public static function rootContextForModule(Router $moduleRouter, IModule $module) : ModuleContext
    {
        return self::rootContext($moduleRouter, $module->getPackageName(), $module->getName(), function () use ($module) {
            return $module;
        });
    }

    /**
     * @return string[]
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * @return string[]
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * @return IModule
     */
    public function getModule() : IModule
    {
        if (!$this->module) {
            $this->module = call_user_func($this->moduleLoaderCallback);
        }

        return $this->module;
    }

    /**
     * @return string
     */
    public function getRootUrl() : string
    {
        return $this->rootUrl;
    }

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    public function getUrl(string $name, array $parameters = []) : string
    {
        return $this->combineUrlPaths($this->rootUrl, $this->urlGenerator->route($name, $parameters, false));
    }

    /**
     * @param string $title
     * @param string $breadcrumbUrl
     * @param string $breadcrumbName
     *
     * @return ModuleContext
     */
    public function withBreadcrumb(string $title, string $breadcrumbUrl, string $breadcrumbName = null) : ModuleContext
    {
        return new ModuleContext(
            $this->moduleRouter,
            $this->rootUrl,
            array_merge($this->titles, [$title]),
            $this->breadcrumbs + [$breadcrumbUrl => $breadcrumbName ?? $title],
            $this->module ?? $this->moduleLoaderCallback
        );
    }

    /**
     * @param IModule $module
     * @param string  $moduleRootPath
     *
     * @return ModuleContext
     */
    public function inSubModuleContext(IModule $module, string $moduleRootPath) : ModuleContext
    {
        return new ModuleContext(
            $this->moduleRouter,
            strpos($moduleRootPath, ':') !== false ? $moduleRootPath : $this->combineUrlPaths($this->rootUrl, $moduleRootPath),
            $this->titles,
            $this->breadcrumbs,
            $module->withoutRequiredPermissions()
        );
    }

    protected function combineUrlPaths(string ... $paths) : string
    {
        $url = array_shift($paths);

        foreach ($paths as $path) {
            $url = rtrim($url, '/') . '/' . ltrim($path, '/');
        }

        return $url;
    }
}