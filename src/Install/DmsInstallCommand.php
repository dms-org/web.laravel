<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Install;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\ICms;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Web\Laravel\DmsServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Composer;

/**
 * The dms:install command
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DmsInstallCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'dms:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the dms in the current fresh laravel project';

    /**
     * @var Composer
     */
    protected $composer;


    /**
     * DmsInstallCommand constructor.
     *
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @param Filesystem $filesystem
     *
     * @throws InvalidOperationException
     */
    public function handle(Filesystem $filesystem, Kernel $console)
    {
        if (!\DB::connection()->getDatabaseName()) {
            throw InvalidOperationException::format('Cannot install: database is required, please verify config');
        }

        $this->disableMySqlStrictMode($filesystem);

        if (!$this->ensureMySqlInnoDbLargePrefixIsEnabled()) {
            return;
        }

        $this->cleanDefaultModelsAndEntities($filesystem);

        $this->scaffoldAppCms($filesystem);

        $this->scaffoldAppOrm($filesystem);

        $this->scaffoldDatabaseSeeders($filesystem);

        $this->scaffoldAppServiceProvider($filesystem);

        $this->publishAssets($console);

        $this->dumpComposerAutoloader();

        $this->setUpInitialDatabase($console);

        $this->addPathsToGitIgnore();

        $this->addDmsUpdateCommandToComposerJsonHook();

        $this->createDefaultDirectories();

        $this->info('Done! Good luck with your project.');
    }

    protected function ensureMySqlInnoDbLargePrefixIsEnabled() : bool
    {
        if (env('DB_CONNECTION') !== 'mysql') {
            return true;
        }

        $hasLargePrefixEnabled = \DB::select('SELECT @@innodb_large_prefix AS flag')[0]->flag;

        if (!$hasLargePrefixEnabled) {
            $this->warn('Please enable innodb_large_prefix. See here https://dev.mysql.com/doc/refman/5.7/en/innodb-parameters.html#sysvar_innodb_large_prefix');
            return false;
        }

        return true;
    }

    /**
     * @param Filesystem $filesystem
     */
    protected function cleanDefaultModelsAndEntities(Filesystem $filesystem)
    {
        $filesystem->deleteDirectory(app_path('Models'));
        $this->info('Deleted: ' . app_path('Models'));
        $filesystem->cleanDirectory(database_path('migrations/'));
        $this->info('Deleted: ' . database_path('migrations/') . '*');
    }

    /**
     * @param Filesystem $filesystem
     */
    protected function disableMySqlStrictMode(Filesystem $filesystem)
    {
        $filesystem->put(config_path('database.php'), preg_replace('/([\'"]strict[\'"]\s*=>\s*)true/', '$1false', file_get_contents(config_path('database.php'))));
        app('config')->set('database.mysql.strict', false);
        $this->info('Disabled MySQL strict mode');
    }

    /**
     * @param Filesystem $filesystem
     */
    protected function scaffoldAppCms(Filesystem $filesystem)
    {
        $filesystem->copy(__DIR__ . '/Stubs/AppCms.php.stub', app_path('AppCms.php'));
        require_once app_path('AppCms.php');
        app()->singleton(ICms::class, \App\AppCms::class);
        $this->info('Created: ' . app_path('AppCms.php'));
    }

    /**
     * @param Filesystem $filesystem
     */
    protected function scaffoldAppOrm(Filesystem $filesystem)
    {
        $filesystem->copy(__DIR__ . '/Stubs/AppOrm.php.stub', app_path('AppOrm.php'));
        require_once app_path('AppOrm.php');
        app()->singleton(IOrm::class, \App\AppOrm::class);
        $this->info('Created: ' . app_path('AppOrm.php'));
    }

    /**
     * @param Filesystem $filesystem
     */
    protected function scaffoldDatabaseSeeders(Filesystem $filesystem)
    {
        $filesystem->copy(__DIR__ . '/Stubs/DmsAdminSeeder.php.stub', database_path('seeders/DmsAdminSeeder.php'));
        $this->info('Created: ' . database_path('seeders/DmsAdminSeeder.php'));
        $filesystem->copy(__DIR__ . '/Stubs/DatabaseSeeder.php.stub', database_path('seeders/DatabaseSeeder.php'));
        $this->info('Updated: ' . database_path('seeders/DatabaseSeeder.php'));
    }

    /**
     * @param Filesystem $filesystem
     */
    protected function scaffoldAppServiceProvider(Filesystem $filesystem)
    {
        $filesystem->copy(__DIR__ . '/Stubs/AppServiceProvider.php.stub', app_path('Providers/AppServiceProvider.php'));
        $this->info('Updated: ' . app_path('Providers/AppServiceProvider.php'));
    }

    /**
     * @param Kernel $console
     */
    protected function publishAssets(Kernel $console)
    {
        $console->call('vendor:publish', ['--provider' => DmsServiceProvider::class]);
        $this->info('Executed: php artisan vendor:publish --provider="' . DmsServiceProvider::class . '"');

        app('config')->set(['dms' => require __DIR__ . '/../../config/dms.php']);
    }

    protected function dumpComposerAutoloader()
    {
        $this->composer->dumpAutoloads();
        $this->info('Executed: composer dump-autoload');
    }

    /**
     * @param Kernel $console
     */
    protected function setUpInitialDatabase(Kernel $console)
    {
        $console->call('dms:make:migration', ['name' => 'initial_db']);
        $this->info('Executed: php artisan dms:make:migration initial_db');

        $console->call('migrate');
        $this->info('Executed: php artisan migrate');

        $console->call('db:seed');
        $this->info('Executed: php artisan db:seed');
    }

    protected function addPathsToGitIgnore()
    {
        file_put_contents(
            app_path('../.gitignore'),
            '/storage/dms/' . PHP_EOL
            . '/public/files/' . PHP_EOL,
            FILE_APPEND
        );
        $this->info('Added to .gitignore');
    }

    protected function addDmsUpdateCommandToComposerJsonHook()
    {
        $composerJsonData                                 = json_decode(file_get_contents(base_path('composer.json')), true);
        $composerJsonData['scripts']['post-update-cmd'][] = 'php artisan dms:update';
        file_put_contents(base_path('composer.json'), json_encode($composerJsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('Added php artisan dms:update to post-update hook in composer.json');
    }

    protected function createDefaultDirectories()
    {
        @mkdir(app_path('Domain/Entities'));
        $this->info('Created the app/Domain/Entities directory');
        @mkdir(app_path('Domain/Services'));
        $this->info('Created the app/Domain/Services directory');
    }
}