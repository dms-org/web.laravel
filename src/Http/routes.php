<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http;

/** @var \Illuminate\Routing\Router $router */

use Dms\Web\Laravel\Error\DmsError;

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

            $router->get('/', 'IndexController@index')->name('index');

            // Files
            $router->post('/file/upload', 'FileController@upload')->name('file.upload');
            $router->get('/file/preview/{token}', 'FileController@preview')->name('file.preview');
            $router->get('/file/download/{token}', 'FileController@download')->name('file.download');

            // Packages
            $router->get(
                'package/{package}/dashboard',
                'Package\PackageController@showDashboard'
            )->name('package.dashboard');

            // Modules
            /** @var ModuleRequestRouter $moduleRouter */
            $moduleRouter = app(ModuleRequestRouter::class);
            $moduleRouter->registerOnMainRouter($router);
        });

        $router->any( '{catch_all}', function () {
            DmsError::abort(404);
        } )->where('catch_all', '(.*)');
    });