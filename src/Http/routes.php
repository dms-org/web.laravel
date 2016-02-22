<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http;

/** @var \Illuminate\Routing\Router $router */
$router    = app('router');
$namespace = __NAMESPACE__ . '\\Controllers';

$router->group(['prefix' => 'dms', 'middleware' => 'dms.web', 'as' => 'dms::', 'namespace' => $namespace],
    function () use ($router) {

        $router->group(['prefix' => 'auth', 'as' => 'auth.', 'namespace' => 'Auth'], function () use ($router) {
            // Authentication Routes...
            $router->get('login', 'AuthController@showLoginForm')->name('login');
            $router->post('login', 'AuthController@login');
            $router->get('logout', 'AuthController@logout')->name('logout');

            // Password Reset Routes...
            $router->get('password/email', 'PasswordController@showResetLinkEmailForm')->name('password.forgot');
            $router->post('password/email', 'PasswordController@sendResetLinkEmail');
            $router->get('password/reset/{token?}', 'PasswordController@showPasswordResetForm')->name('password.reset');
            $router->post('password/reset', 'PasswordController@reset');
        });

        $router->group(['middleware' => 'dms.auth'], function () use ($router) {

            // Files
            $router->get('/file/upload', 'FileController@upload')->name('file.upload');
            $router->get('/file/download/{token}', 'FileController@download')->name('file.download');

            // Account
            $router->get('/', 'IndexController@index')->name('index');
            $router->get('/user/profile', 'UserController@showProfileForm')->name('auth.user.profile');
            $router->post('/user/profile', 'UserController@updateUserProfile')->name('auth.user.profile.submit');

            // Packages
            $router->get(
                'package/{package}/dashboard',
                'Package\PackageController@showDashboard'
            )->name('package.dashboard');

            // Modules
            $router->get(
                'package/{package}/{module}',
                'Package\ModuleController@showDashboard'
            )->name('package.module.dashboard');

            // Actions
            $router->get(
                'package/{package}/{module}/action/{action}/form/{object_id?}',
                'Package\ActionController@showForm'
            )->name('package.module.action.form');

            $router->post(
                'package/{package}/{module}/action/{action}/form/stage/{number?}',
                'Package\ActionController@getFormStage'
            )->name('package.module.action.form.stage');

            $router->post(
                'package/{package}/{module}/action/{action}/run',
                'Package\ActionController@runAction'
            )->name('package.module.action.run');

            $router->get(
                'package/{package}/{module}/action/{action}/show/{object_id?}',
                'Package\ActionController@showActionResult'
            )->name('package.module.action.show');

            // Tables
            $router->get(
                'package/{package}/{module}/table/{table}/{view}',
                'Package\TableController@showTable'
            )->name('package.module.table.view.show');

            $router->post(
                'package/{package}/{module}/table/{table}/{view}/reorder',
                'Package\TableController@reorderRow'
            )->name('package.module.table.view.reorder');

            $router->post(
                'package/{package}/{module}/table/{table}/{view}/load',
                'Package\TableController@loadTableRows'
            )->name('package.module.table.view.load');

            // Charts
            $router->get(
                'package/{package}/{module}/chart/{chart}/{view}',
                'Package\ChartController@showChart'
            )->name('package.module.chart.view.show');

            $router->post(
                'package/{package}/{module}/chart/{chart}/{view}/reorder',
                'Package\ChartController@loadChartData'
            )->name('package.module.chart.view.load');
        });
    });
