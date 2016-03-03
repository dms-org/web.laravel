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
     * @var IModule
     */
    protected $module;

    /**
     * ModuleContext constructor.
     *
     * @param Router    $moduleRouter
     * @param string    $rootUrl
     * @param array     $titles
     * @param \string[] $breadcrumbs
     * @param IModule   $module
     */
    public function __construct(Router $moduleRouter, string $rootUrl, array $titles, array $breadcrumbs, IModule $module)
    {
        $this->moduleRouter = $moduleRouter;
        $this->urlGenerator = new UrlGenerator($moduleRouter->getRoutes(), request());
        $this->rootUrl      = $rootUrl;
        $this->titles       = $titles;
        $this->breadcrumbs  = $breadcrumbs;
        $this->module       = $module;
    }

    /**
     * @param Router  $moduleRouter
     * @param IModule $module
     *
     * @return ModuleContext
     */
    public static function rootContext(Router $moduleRouter, IModule $module) : ModuleContext
    {
        $packageName = $module->getPackageName();
        $moduleName  = $module->getName();

        return new ModuleContext(
            $moduleRouter,
            route('dms::package.module.dashboard', [$packageName, $moduleName]),
            [StringHumanizer::title($packageName), StringHumanizer::title($moduleName)],
            [
                route('dms::index')                                                 => 'Home',
                route('dms::package.dashboard', [$packageName])                     => StringHumanizer::title($packageName),
                route('dms::package.module.dashboard', [$packageName, $moduleName]) => StringHumanizer::title($moduleName),
            ],
            $module
        );
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
        return $this->module;
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
            $this->module
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
            $module
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