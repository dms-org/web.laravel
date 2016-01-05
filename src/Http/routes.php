<?php

namespace Dms\Web\Laravel\Http;

/** @var \Illuminate\Routing\Router $router */
$router    = app('router');
$namespace = __NAMESPACE__ . '\\Controllers';

$router->group(['prefix' => 'dms', 'middleware' => 'dms.web', 'as' => 'dms::', 'namespace' => $namespace], function () use ($router) {

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

        $router->get('/', 'IndexController@index')->name('index');

        $router->group(['prefix' => 'package/{package}', 'as' => 'package.', 'namespace' => 'Package'], function () use ($router) {

            $router->get('dashboard', 'PackageController@showDashboard')->name('dashboard');

            $router->group(['prefix' => '{module}', 'as' => 'module.'], function () use ($router) {
                $router->get('/', 'ModuleController@index')->name('index');

                $router->group(['prefix' => '{action}', 'as' => 'action.'], function () use ($router) {
                    $router->get('/', 'ActionController@getInfo')->name('info');
                    $router->get('form', 'ActionController@showForm')->name('form');
                    $router->post('form/stage/{number}', 'ActionController@getFormStage')->name('form.stage');
                    $router->post('run', 'ActionController@run')->name('run');
                });
            });
        });
    });
});
