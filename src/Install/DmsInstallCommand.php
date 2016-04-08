<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Install;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\ICms;
use Dms\Core\Persistence\Db\Mapping\IOrm;
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
    public function fire(Filesystem $filesystem, Kernel $console)
    {
        if (!\DB::connection()->getDatabaseName()) {
            throw InvalidOperationException::format('Cannot install: database is required, please verify config');
        }

        $filesystem->delete(app_path('User.php'));
        $this->info('Deleted: ' . app_path('User.php'));
        $filesystem->cleanDirectory(database_path('migrations/'));
        $this->info('Deleted: ' . database_path('migrations/') . '*');

        $filesystem->copy(__DIR__ . '/Stubs/AppCms.php.stub', app_path('AppCms.php'));
        require_once app_path('AppCms.php');
        app()->singleton(ICms::class, \App\AppCms::class);
        $this->info('Created: ' . app_path('AppCms.php'));

        $filesystem->copy(__DIR__ . '/Stubs/AppOrm.php.stub', app_path('AppOrm.php'));
        require_once app_path('AppOrm.php');
        app()->singleton(IOrm::class, \App\AppOrm::class);
        $this->info('Created: ' . app_path('AppOrm.php'));

        $filesystem->copy(__DIR__ . '/Stubs/DmsAdminSeeder.php.stub', database_path('seeds/DmsAdminSeeder.php'));
        $this->info('Created: ' . database_path('seeds/DmsAdminSeeder.php'));
        $filesystem->copy(__DIR__ . '/Stubs/DatabaseSeeder.php.stub', database_path('seeds/DatabaseSeeder.php'));
        $this->info('Updated: ' . database_path('seeds/DatabaseSeeder.php'));

        $filesystem->copy(__DIR__ . '/Stubs/AppServiceProvider.php.stub', app_path('Providers/AppServiceProvider.php'));
        $this->info('Updated: ' . app_path('Providers/AppServiceProvider.php'));

        $console->call('vendor:publish');
        $this->info('Executed: php artisan vendor:publish');

        app('config')->set(['dms' => require __DIR__ . '/../../config/dms.php']);

        $this->composer->dumpAutoloads();
        $this->info('Executed: composer dump-autoload');

        $console->call('dms:make:migration', ['name' => 'initial_db']);
        $this->info('Executed: php artisan dms:make:migration initial_db');

        $console->call('migrate');
        $this->info('Executed: php artisan migrate');

        $console->call('db:seed');
        $this->info('Executed: php artisan db:seed');

        file_put_contents(
            app_path('../.gitignore'),
            '/storage/dms/' . PHP_EOL
            . '/public/files/' . PHP_EOL,
            FILE_APPEND
        );
        $this->info('Added to .gitignore');

        $this->info('Done! Good luck with your project.');
    }
}