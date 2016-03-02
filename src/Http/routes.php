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
            $router->post('/file/upload', 'FileController@upload')->name('file.upload');
            $router->get('/file/preview/{token}', 'FileController@preview')->name('file.preview');
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

            $moduleRouter = new ModuleRequestRouter();
            $moduleRouter->registerOnMainRouter($router);
        });
    });
