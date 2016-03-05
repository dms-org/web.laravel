<?php

namespace Dms\Web\Laravel\Http;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\ICms;
use Dms\Core\Module\IModule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

/**
 * The module request router
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleRequestRouter
{
    /**
     * @var $moduleContext []
     */
    protected $currentModuleContextStack = [];

    /**
     * @var Router
     */
    protected $router;

    public function __construct()
    {
        $this->router = $this->loadRoutes();
    }

    protected function loadRoutes() : Router
    {
        $router = new Router(app(Dispatcher::class), app());

        $router->group(['namespace' => '\\' . __NAMESPACE__ . '\\Controllers'], function () use ($router) {

            $router->get(
                '/',
                'Package\Module\ModuleController@showDashboard'
            )->name('dashboard');

            // Actions
            $router->get(
                '/action/{action}/form/{object_id?}',
                'Package\Module\ActionController@showForm'
            )->name('action.form');

            $router->post(
                '/action/{action}/form/stage/{stage}',
                'Package\Module\ActionController@getFormStage'
            )->name('action.form.stage');

            $router->any(
                '/action/{action}/form/stage/{stage}/field/{field_name}/{field_action?}',
                'Package\Module\ActionController@runFieldRendererAction'
            )->where('field_action', '(.*)')->name('action.form.stage.field.action');

            $router->any(
                '/action/{action}/form/{object_id}/stage/{stage}/field/{field_name}/{field_action?}',
                'Package\Module\ActionController@runFieldRendererActionWithObject'
            )->where('field_action', '(.*)')->name('action.form.object.stage.field.action');

            $router->post(
                '/action/{action}/run',
                'Package\Module\ActionController@runAction'
            )->name('action.run');

            $router->get(
                '/action/{action}/show/{object_id?}',
                'Package\Module\ActionController@showActionResult'
            )->name('action.show');

            // Tables
            $router->get(
                '/table/{table}/{view}',
                'Package\Module\TableController@showTable'
            )->name('table.view.show');

            $router->post(
                '/table/{table}/{view}/reorder',
                'Package\Module\TableController@reorderRow'
            )->name('table.view.reorder');

            $router->post(
                '/table/{table}/{view}/load',
                'Package\Module\TableController@loadTableRows'
            )->name('table.view.load');

            // Charts
            $router->get(
                '/chart/{chart}/{view}',
                'Package\Module\ChartController@showChart'
            )->name('chart.view.show');

            $router->post(
                '/chart/{chart}/{view}/load',
                'Package\Module\ChartController@loadChartData'
            )->name('chart.view.load');
        });

        $router->matched(function (RouteMatched $event) {
            if ($this->currentModuleContextStack && !$event->route->parameter('module')) {
                // Prepend module context as first parameter
                $allParameters = $event->route->parameters();
                $allParameters = ['module' => $this->getCurrentModuleContext()] + $allParameters;

                foreach ($allParameters as $parameterName => $value) {
                    $event->route->forgetParameter($parameterName);
                }

                foreach ($allParameters as $parameterName => $value) {
                    $event->route->setParameter($parameterName, $value);
                }
            }
        });

        $router->getRoutes()->refreshNameLookups();

        return $router;
    }

    /**
     * @return ModuleContext
     */
    public static function currentModuleContext() : ModuleContext
    {
        return app(__CLASS__)->getCurrentModuleContext();
    }

    /**
     * @return Router
     */
    public function getRouter() : Router
    {
        return $this->router;
    }

    /**
     * @return ModuleContext
     * @throws InvalidOperationException
     */
    public function getCurrentModuleContext() : ModuleContext
    {
        if (empty($this->currentModuleContextStack)) {
            throw InvalidOperationException::format('Not in a valid module context');
        }

        return end($this->currentModuleContextStack);
    }

    /**
     * @param ModuleContext $moduleContext
     * @param Request       $request
     *
     * @return Response
     */
    public function dispatch(ModuleContext $moduleContext, Request $request) : Response
    {
        $this->currentModuleContextStack[] = $moduleContext;

        $originalMiddlewareFlag            = app()->bound('middleware.disable') ? app()->make('middleware.disable') : false;
        $originalRequest                   = app()->bound('request') ? app()->make('request') : null;

        app()->instance('middleware.disable', true);
        app()->instance('request', $request);

        $response = $this->router->dispatch($request);

        app()->instance('middleware.disable', $originalMiddlewareFlag);
        app()->instance('request', $originalRequest);
        array_pop($this->currentModuleContextStack);

        return $response;
    }

    public function getRootContext(IModule $module) : ModuleContext
    {
        $moduleContext = ModuleContext::rootContext($this->router, $module);
        $this->currentModuleContextStack = [$moduleContext];

        return $moduleContext;
    }

    /**
     * @param Router $router
     *
     * @return void
     */
    public function registerOnMainRouter(Router $router)
    {
        $router->group(['prefix' => '/package/{package}/{module}', 'as' => 'package.module.'], function () use ($router) {
            $groupStack   = $router->getGroupStack();
            $currentGroup = end($groupStack);

            foreach ($this->router->getRoutes()->getRoutes() as $route) {
                /** @var Route $route */
                $newRoute = clone $route;
                if ($newRoute->getUri() === '/') {
                    $newRoute->setUri($currentGroup['prefix']);
                } else {
                    $newRoute->setUri($currentGroup['prefix'] . '/' . rtrim($newRoute->getUri(), '/'));
                }
                $newRoute->setAction(Router::mergeGroup($newRoute->getAction(), $currentGroup));
                $router->getRoutes()->add($newRoute);
            }
        });

        $router->bind('module', function ($value, Route $route) {
            /** @var ICms $cms */
            $cms = app(ICms::class);

            $packageName = $route->parameter('package');
            $moduleName  = $route->parameter('module');
            $route->forgetParameter('package');

            if (!$cms->hasPackage($packageName)) {
                abort(404);
            }

            $package = $cms->loadPackage($packageName);

            if (!$package->hasModule($moduleName)) {
                abort(404);
            }

            return $this->getRootContext($package->loadModule($moduleName));
        });
    }
}