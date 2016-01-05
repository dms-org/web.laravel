<?php

namespace Dms\Web\Laravel;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IRoleRepository;
use Dms\Core\Auth\IUserRepository;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Doctrine\DoctrineConnection;
use Dms\Web\Laravel\Auth\DmsUserProvider;
use Dms\Web\Laravel\Auth\LaravelAuthSystem;
use Dms\Web\Laravel\Auth\Password\BcryptPasswordHasher;
use Dms\Web\Laravel\Auth\Password\IPasswordHasherFactory;
use Dms\Web\Laravel\Auth\Password\IPasswordResetService;
use Dms\Web\Laravel\Auth\Password\PasswordHasherFactory;
use Dms\Web\Laravel\Auth\Password\PasswordResetService;
use Dms\Web\Laravel\Auth\Persistence\RoleRepository;
use Dms\Web\Laravel\Auth\Persistence\UserRepository;
use Dms\Web\Laravel\Http\Middleware\Authenticate;
use Dms\Web\Laravel\Http\Middleware\EncryptCookies;
use Dms\Web\Laravel\Http\Middleware\RedirectIfAuthenticated;
use Dms\Web\Laravel\Http\Middleware\VerifyCsrfToken;
use Dms\Web\Laravel\Language\LaravelLanguageProvider;
use Dms\Web\Laravel\Persistence\Db\Migration\AutoGenerateMigrationCommand;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\Container;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Database\Connection;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Router;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Interop\Container\ContainerInterface;
use Monii\Interop\Container\Laravel\LaravelContainer as ContainerInteropAdapter;

/**
 * The DMS service provider
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DmsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerIocContainer();
        $this->registerAuth();
        $this->registerLang();
        $this->registerHttpRoutes();
        $this->registerMiddleware();
        $this->registerDbConnection();
        $this->registerCommands();
        $this->publishAssets();
        $this->publishConfig();
        $this->publishSeeders();
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->loadViews();
        $this->loadTranslations();
    }

    private function publishAssets()
    {
        $this->publishes([
                __DIR__ . '/../dist/' => public_path('vendor/dms/'),
        ], 'assets');
    }

    private function publishConfig()
    {
        $this->publishes([
                __DIR__ . '/Config/config/dms.php' => config_path('dms.php'),
        ]);
    }

    private function registerIocContainer()
    {
        $this->app->singleton(ContainerInterface::class, function (Container $laravelContainer) {
            return new ContainerInteropAdapter($laravelContainer);
        });
    }

    private function registerAuth()
    {
        $this->app->singleton(DmsUserProvider::class, DmsUserProvider::class);
        $this->app->singleton(IAuthSystem::class, LaravelAuthSystem::class);

        $this->app->singleton(IPasswordHasherFactory::class, function () {
            return new PasswordHasherFactory(
                    [
                            BcryptPasswordHasher::ALGORITHM => function ($costFactor) {
                                return new BcryptPasswordHasher($costFactor);
                            },
                    ],
                    BcryptPasswordHasher::ALGORITHM,
                    10
            );
        });

        $this->app->bind(IUserRepository::class, UserRepository::class);
        $this->app->bind(IRoleRepository::class, RoleRepository::class);
        $this->app->bind(IPasswordResetService::class, PasswordResetService::class);

        /** @var AuthManager $auth */
        $auth = $this->app['auth'];

        $auth->provider('dms', function (Container $app) {
            return $app->make(DmsUserProvider::class);
        });

        $this->app['config']->set('auth.guards.dms', [
                'driver'   => 'session',
                'provider' => 'dms-users',
        ]);

        $this->app['config']->set('auth.providers.dms-users', [
                'driver' => 'dms',
                'model'  => Auth\User::class,
        ]);

        $this->app['config']->set('auth.passwords.dms', [
                'provider' => 'dms-users',
                'email'    => 'dms::auth.email.password',
                'table'    => 'dms_password_resets',
                'expire'   => 60,
        ]);
    }

    private function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang/', 'dms');
    }

    private function registerLang()
    {
        $this->app->bind(ILanguageProvider::class, LaravelLanguageProvider::class);
    }

    private function registerHttpRoutes()
    {
        if (!method_exists($this->app, 'routesAreCaches') || !$this->app->routesAreCached()) {
            require __DIR__ . '/Http/routes.php';
        }
    }

    private function registerMiddleware()
    {
        /** @var Router $router */
        $router = $this->app['router'];

        $router->middlewareGroup('dms.web', [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
        ]);

        $router->middleware('dms.auth', Authenticate::class);
        $router->middleware('dms.guest', RedirectIfAuthenticated::class);
        $router->middleware('dms.throttle', ThrottleRequests::class);
    }

    private function loadViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dms');
    }

    private function registerDbConnection()
    {
        $this->app->singleton(IConnection::class, function () {
            /** @var Connection $connection */
            $connection = $this->app->make(Connection::class);

            return new DoctrineConnection($connection->getDoctrineConnection());
        });
    }

    private function registerCommands()
    {
        $this->commands([
                AutoGenerateMigrationCommand::class,
        ]);
    }

    private function publishSeeders()
    {
        $this->publishes([
                __DIR__ . '/Persistence/Db/Seeders/' => database_path('seeds'),
        ]);
    }
}