<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Http;

/** @var \Illuminate\Routing\Router $router */
$router = app('router');
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

            $router->group(['prefix' => 'file/', 'as' => 'file.'], function () use ($router) {
                $router->get('/upload', 'FileController@upload')->name('upload');
                $router->get('/download/{token}', 'FileController@download')->name('download');
            });

            $router->get('/', 'IndexController@index')->name('index');
            $router->post('search', 'IndexController@searchSystem')->name('search');
            $router->get('/user/profile', 'UserController@showProfileForm')->name('auth.user.profile');
            $router->post('/user/profile', 'UserController@updateUserProfile')->name('auth.user.profile.submit');

            $router->group(['prefix' => 'package/{package}', 'as' => 'package.', 'namespace' => 'Package'],
                function () use ($router) {

                    $router->get('dashboard', 'PackageController@showDashboard')->name('dashboard');

                    $router->group(['prefix' => '{module}', 'as' => 'module.'], function () use ($router) {
                        $router->get('/', 'ModuleController@showDashboard')->name('dashboard');

                        $router->group(['prefix' => 'action/{action}', 'as' => 'action.'], function () use ($router) {
                            $router->get('form/{object_id?}', 'ActionController@showForm')->name('form');
                            $router->post('form/stage/{number}', 'ActionController@getFormStage')->name('form.stage');
                            $router->post('run', 'ActionController@runAction')->name('run');
                        });

                        $router->group(['prefix' => 'table/{table}/{view}', 'as' => 'table.view.'], function () use ($router) {
                            $router->get('/', 'TableController@showTable')->name('show');
                            $router->get('reorder', 'TableController@reorderRow')->name('reorder');
                            $router->post('load', 'TableController@loadTableRows')->name('load');
                        });

                        $router->group(['prefix' => 'chart/{chart}/{view}', 'as' => 'chart.view'], function () use ($router) {
                            $router->get('/', 'ChartController@showChart')->name('show');
                            $router->post('load', 'ChartController@loadChartData')->name('load');
                        });
                    });
                });
        });
    });
