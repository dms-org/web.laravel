<?php declare(strict_types = 1);

namespace Dms\Web\Laravel;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IRoleRepository;
use Dms\Core\Auth\IAdminRepository;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Util\DateTimeClock;
use Dms\Core\Util\IClock;
use Dms\Web\Laravel\Action\ActionExceptionHandlerCollection;
use Dms\Web\Laravel\Action\ActionInputTransformerCollection;
use Dms\Web\Laravel\Action\ActionResultHandlerCollection;
use Dms\Web\Laravel\Auth\DmsUserProvider;
use Dms\Web\Laravel\Auth\LaravelAuthSystem;
use Dms\Web\Laravel\Auth\Password\BcryptPasswordHasher;
use Dms\Web\Laravel\Auth\Password\IPasswordHasherFactory;
use Dms\Web\Laravel\Auth\Password\IPasswordResetService;
use Dms\Web\Laravel\Auth\Password\PasswordHasherFactory;
use Dms\Web\Laravel\Auth\Password\PasswordResetService;
use Dms\Web\Laravel\Auth\Persistence\RoleRepository;
use Dms\Web\Laravel\Auth\Persistence\AdminRepository;
use Dms\Web\Laravel\Document\DirectoryTree;
use Dms\Web\Laravel\Document\PublicFileModule;
use Dms\Web\Laravel\File\Command\ClearTempFilesCommand;
use Dms\Web\Laravel\File\ITemporaryFileService;
use Dms\Web\Laravel\File\Persistence\ITemporaryFileRepository;
use Dms\Web\Laravel\File\Persistence\TemporaryFileRepository;
use Dms\Web\Laravel\File\TemporaryFileService;
use Dms\Web\Laravel\Http\Middleware\Authenticate;
use Dms\Web\Laravel\Http\Middleware\EncryptCookies;
use Dms\Web\Laravel\Http\Middleware\RedirectIfAuthenticated;
use Dms\Web\Laravel\Http\Middleware\VerifyCsrfToken;
use Dms\Web\Laravel\Http\ModuleRequestRouter;
use Dms\Web\Laravel\Install\DmsInstallCommand;
use Dms\Web\Laravel\Language\LaravelLanguageProvider;
use Dms\Web\Laravel\Persistence\Db\DmsOrm;
use Dms\Web\Laravel\Persistence\Db\LaravelConnection;
use Dms\Web\Laravel\Persistence\Db\Migration\AutoGenerateMigrationCommand;
use Dms\Web\Laravel\Renderer\Chart\ChartRendererCollection;
use Dms\Web\Laravel\Renderer\Form\FieldRendererCollection;
use Dms\Web\Laravel\Renderer\Module\ModuleRendererCollection;
use Dms\Web\Laravel\Renderer\Package\PackageRendererCollection;
use Dms\Web\Laravel\Renderer\Table\ColumnComponentRendererCollection;
use Dms\Web\Laravel\Renderer\Table\ColumnRendererFactoryCollection;
use Dms\Web\Laravel\Renderer\Widget\WidgetRendererCollection;
use Dms\Web\Laravel\View\DmsNavigationViewComposer;
use Illuminate\Auth\AuthManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\Container;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Application;
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
     * @throws InvalidOperationException
     */
    public function register()
    {
        if (!$this->isRunningInConsole() && !is_array($this->app['config']->get('dms'))) {
            throw InvalidOperationException::format(
                'Cannot find dms config file: did you forget to run `php artisan vendor:publish` ?'
            );
        }

        $this->registerIocContainer();
        $this->registerAuth();
        $this->registerLang();
        $this->registerModuleServices();
        $this->registerModules();
        $this->registerHttpRoutes();
        $this->registerMiddleware();
        $this->registerDbConnection();
        $this->registerUtils();
        $this->registerActionServices();
        $this->registerRenderers();
        $this->registerViewComposers();

        if ($this->isRunningInConsole()) {
            $this->registerCommands();
            $this->registerSchedule();
            $this->publishAssets();
            $this->publishConfig();
            $this->publishSeeders();
        }
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
            __DIR__ . '/../config/dms.php' => config_path('dms.php'),
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

        $this->app->bind(IAdminRepository::class, AdminRepository::class);
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
            'model'  => Auth\Admin::class,
        ]);

        $this->app['config']->set('auth.passwords.dms', [
            'provider' => 'dms-users',
            'email'    => 'dms::auth.email.password',
            'table'    => DmsOrm::NAMESPACE . 'password_resets',
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

    public function registerModuleServices()
    {
        $this->app->singleton(ModuleRequestRouter::class);
    }

    public function registerModules()
    {
        $this->app->bind(PublicFileModule::class, function () {
            return new PublicFileModule(
                DirectoryTree::from($this->app['config']->get('dms.storage.public-files.dir')),
                DirectoryTree::from($this->app['config']->get('dms.storage.trashed-files.dir')),
                $this->app[IAuthSystem::class]
            );
        });
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
        // Ensure the mysql returns the number of matched rows (instead of affected)
        // rows for update / delete queries
        foreach ($this->app['config']->get('database.connections') as $key => $config) {
            if ($config['driver'] === 'mysql') {
                $config['options'][\PDO::MYSQL_ATTR_FOUND_ROWS] = true;

                $this->app['config']->set('database.connections.' . $key, $config);
            }
        }

        $this->app->singleton(IConnection::class, function () {
            /** @var Connection $connection */
            $connection = $this->app->make(Connection::class);

            if ($connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql'
                && version_compare($connection->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION), '5.7.6', '>=')
            ) {
                $connection->statement('SET optimizer_switch = \'derived_merge=off\'');
            }

            return new LaravelConnection($connection);
        });
    }

    private function registerCommands()
    {
        $this->commands([
            DmsInstallCommand::class,
            AutoGenerateMigrationCommand::class,
            ClearTempFilesCommand::class,
        ]);
    }

    private function registerSchedule()
    {
        /** @var Schedule $schedule */
        $schedule = $this->app[Schedule::class];

        $schedule->command('dms:clear-temp-files')->daily();
    }

    private function publishSeeders()
    {
        $this->publishes([
            __DIR__ . '/Persistence/Db/Seeders/' => database_path('seeds'),
        ]);
    }

    private function registerUtils()
    {
        $this->app->bind(IClock::class, DateTimeClock::class);
        $this->app->bind(ITemporaryFileService::class, TemporaryFileService::class);
        $this->app->bind(ITemporaryFileRepository::class, TemporaryFileRepository::class);
    }

    private function registerActionServices()
    {
        $this->app->singleton(ActionInputTransformerCollection::class, function () {
            return new ActionInputTransformerCollection($this->makeAll(
                config('dms.services.actions.input-transformers')
            ));
        });

        $this->app->singleton(ActionResultHandlerCollection::class, function () {
            return new ActionResultHandlerCollection($this->makeAll(
                config('dms.services.actions.result-handlers')
            ));
        });

        $this->app->singleton(ActionExceptionHandlerCollection::class, function () {
            return new ActionExceptionHandlerCollection($this->makeAll(
                config('dms.services.actions.exception-handlers')
            ));
        });
    }

    private function registerRenderers()
    {
        $this->app->singleton(FieldRendererCollection::class, function () {
            return new FieldRendererCollection($this->makeAll(
                config('dms.services.renderers.form-fields')
            ));
        });

        $this->app->singleton(ColumnComponentRendererCollection::class, function () {
            return new ColumnComponentRendererCollection($this->makeAll(
                array_merge(
                    config('dms.services.renderers.table.column-components'),
                    config('dms.services.renderers.form-fields')
                )
            ));
        });

        $this->app->singleton(ColumnRendererFactoryCollection::class, function () {
            return new ColumnRendererFactoryCollection(
                $this->app->make(ColumnComponentRendererCollection::class),
                $this->makeAll(
                    config('dms.services.renderers.table.columns')
                )
            );
        });

        $this->app->singleton(ChartRendererCollection::class, function () {
            return new ChartRendererCollection($this->makeAll(
                config('dms.services.renderers.charts')
            ));
        });

        $this->app->singleton(WidgetRendererCollection::class, function () {
            return new WidgetRendererCollection($this->makeAll(
                config('dms.services.renderers.widgets')
            ));
        });

        $this->app->singleton(ModuleRendererCollection::class, function () {
            return new ModuleRendererCollection($this->makeAll(
                config('dms.services.renderers.modules')
            ));
        });

        $this->app->singleton(PackageRendererCollection::class, function () {
            return new PackageRendererCollection($this->makeAll(
                config('dms.services.renderers.packages')
            ));
        });
    }

    private function makeAll(array $services)
    {
        foreach ($services as $key => $service) {
            $services[$key] = $this->app->make($service);
        }

        return $services;
    }

    private function registerViewComposers()
    {
        view()->composer('dms::template.default', DmsNavigationViewComposer::class);
    }

    /**
     * @return bool
     */
    protected function isRunningInConsole()
    {
        return $this->app instanceof Application && $this->app->runningInConsole();
    }
}