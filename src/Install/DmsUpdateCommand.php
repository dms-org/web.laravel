<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Install;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Web\Laravel\DmsServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\Kernel;

/**
 * The dms:update command
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DmsUpdateCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'dms:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the dms assets';

    /**
     * Execute the console command.
     *
     * @param Filesystem $filesystem
     *
     * @throws InvalidOperationException
     */
    public function fire(Kernel $console)
    {
        $console->call('vendor:publish', ['provider' => DmsServiceProvider::class, 'tag' => 'public', 'force' => true]);
        $this->info('Executed: php artisan vendor:publish --provider="' . DmsServiceProvider::class . '" --tag="public" --force');

        $this->info('Done!');
    }
}