<?php

namespace Dms\Web\Laravel\Http;

use Dms\Core\ICms;
use Dms\Core\Module\IModule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
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
     * @var Router
     */
    protected $router;

    public function __construct()
    {
        $this->router = $this->loadRoutes();
    }

    protected function loadRoutes() : Router
    {
        $router = new Router(app(Dispatcher::class));

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
            '/action/{action}/form/stage/{number}',
            'Package\Module\ActionController@getFormStage'
        )->name('action.form.stage');

        $router->post(
            '/action/{action}/form/field/stage/{number}/{field_name}/{field_action}',
            'Package\Module\ActionController@runFieldRendererAction'
        )->name('action.form.stage.field.action');

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

        return $router;
    }

    /**
     * @return Router
     */
    public function getRouter() : Router
    {
        return $this->router;
    }

    /**
     * @param IModule $module
     * @param Request $request
     *
     * @return Response
     */
    public function dispatch(IModule $module, Request $request) : Response
    {
        $this->router->bind('module', $module);
        return $this->router->dispatch($request);
    }

    public function registerOnMainRouter(Router $router)
    {
        $router->group(['prefix' => '/package/{package}/{module}', 'as' => 'package.module.'], function () use ($router) {
            foreach ($this->router->getRoutes()->getRoutes() as $route) {
                /** @var Route $route */
                $router->match($route->getMethods(), $route->getUri(), $route->getAction());
            }
        });

        $router->bind('module', function ($value, Route $route) {
            /** @var ICms $cms */
            $cms = app(ICms::class);

            $package = $route->parameter('package');
            $route->forgetParameter('package');

            return $cms
                ->loadPackage($package)
                ->loadModule($route->parameter('module'));
        });
    }
}