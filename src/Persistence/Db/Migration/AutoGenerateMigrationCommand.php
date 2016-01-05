<?php

namespace Dms\Web\Laravel\Persistence\Db\Migration;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Doctrine\DoctrineConnection;
use Dms\Core\Persistence\Db\Doctrine\Migration\MigrationGenerator;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Support\Composer;

/**
 * The auto migrate command
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AutoGenerateMigrationCommand extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'dms:make:migration {name : The name of the migration.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-generates a new migration file to sync the db with the current state of the orm';

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var MigrationGenerator
     */
    private $autoMigrationGenerator;

    /**
     * @var DoctrineConnection
     */
    private $connection;

    /**
     * @var IOrm
     */
    private $orm;

    /**
     * AutoGenerateMigrationCommand constructor.
     *
     * @param Composer                  $composer
     * @param LaravelMigrationGenerator $autoMigrationGenerator
     * @param IConnection               $connection
     * @param IOrm                      $orm
     */
    public function __construct(
            Composer $composer,
            LaravelMigrationGenerator $autoMigrationGenerator,
            IConnection $connection,
            IOrm $orm
    ) {
        parent::__construct();

        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'connection', $connection, DoctrineConnection::class);

        $this->composer               = $composer;
        $this->autoMigrationGenerator = $autoMigrationGenerator;
        $this->connection             = $connection;
        $this->orm                    = $orm;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $file = $this->autoMigrationGenerator->generateMigration(
                $this->connection,
                $this->orm,
                $this->input->getArgument('name')
        );

        if (!$file) {
            $this->line("<info>No Migration Generated: Schema has not changed.</info>");
        } else {
            $this->line("<info>Created Migration:</info> {$file}");
        }

        $this->composer->dumpAutoloads();
    }

}