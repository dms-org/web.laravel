<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Persistence\Db\Migration;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Doctrine\Migration\MigrationGenerator;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\BaseEnumType;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\MediumIntType;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\TinyIntType;
use Dms\Web\Laravel\Util\PhpBuilder;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use MJS\TopSort\Implementations\StringSort;

/**
 * The laravel migration generator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LaravelMigrationGenerator extends MigrationGenerator
{
    /**
     * @var MigrationCreator
     */
    private $laravelMigrationCreator;

    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var string[]
     */
    protected $tablesToIgnore = ['migrations'];

    /**
     * @var string
     */
    private $path;

    /**
     * LaravelMigrationGenerator constructor.
     *
     * @param MigrationCreator $laravelMigrationCreator
     * @param Filesystem       $files
     * @param string|null      $path
     */
    public function __construct(MigrationCreator $laravelMigrationCreator, Filesystem $files, string $path = null)
    {
        parent::__construct();

        $this->laravelMigrationCreator = $laravelMigrationCreator;
        $this->files                   = $files;
        $this->path                    = $path ?: database_path('migrations');
    }

    /**
     * @param SchemaDiff $diff
     * @param SchemaDiff $reverseDiff
     * @param string     $migrationName
     *
     * @return string|null
     */
    protected function createMigration(SchemaDiff $diff, SchemaDiff $reverseDiff, string $migrationName)
    {
        $this->filterDiff($diff);
        $this->filterDiff($reverseDiff);

        if ($this->isSchemaDiffEmpty($diff) && $this->isSchemaDiffEmpty($reverseDiff)) {
            return null;
        }

        $upCode   = $this->createMigrationCode($diff);
        $downCode = $this->createMigrationCode($reverseDiff);

        $migrationFile     = $this->laravelMigrationCreator->create($migrationName, $this->path);
        $migrationContents = $this->files->get($migrationFile);

        $migrationContents = $this->replaceMethodBody('up', $upCode, $migrationContents);
        $migrationContents = $this->replaceMethodBody('down', $downCode, $migrationContents);

        $this->files->put($migrationFile, $migrationContents);

        return $migrationFile;
    }

    protected function filterDiff(SchemaDiff $diff)
    {
        $this->removeIgnoredTables($diff->newTables);
        $this->removeIgnoredTables($diff->removedTables);
        $this->removeIgnoredTables($diff->changedTables);

        $this->orderTablesByForeignKeyDependency($diff->newTables);
        $this->orderTablesByForeignKeyDependency($diff->changedTables);
        $this->orderTablesByForeignKeyDependency($diff->removedTables, $reverse = true);
    }

    protected function removeIgnoredTables(array &$tables)
    {
        foreach ($tables as $key => $table) {
            $tableName = $table instanceof TableDiff
                ? $table->getNewName()
                : $table->getName();

            if (in_array($tableName, $this->tablesToIgnore, true)) {
                unset($tables[$key]);
            }
        }
    }

    /**
     * @param Table[]|TableDiff[] $tables
     * @param bool                $reverse
     *
     * @return void
     */
    protected function orderTablesByForeignKeyDependency(array &$tables, bool $reverse = false)
    {
        if (empty($tables)) {
            return;
        }

        $topologicalSorter = new StringSort();

        $indexedTables = [];

        foreach ($tables as $table) {
            $tableName = $table instanceof TableDiff
                ? $table->getNewName()
                : $table->getName();

            $originalTable = $table instanceof TableDiff
                ? $table->fromTable
                : $table;

            $topologicalSorter->add($tableName, $this->getTableDependencies($originalTable));
            $indexedTables[$tableName] = $table;
        }

        $sortedTableNames = $topologicalSorter->sort();
        $sortedTables     = [];

        foreach ($sortedTableNames as $tableName) {
            $sortedTables[$tableName] = $indexedTables[$tableName];
        }

        $tables = $reverse
            ? array_reverse($sortedTables)
            : $sortedTables;
    }

    /**
     * @param Table $table
     *
     * @return array|\string[]
     * @throws InvalidArgumentException
     */
    protected function getTableDependencies(Table $table) : array
    {
        $tableDependencies = [];

        foreach ($table->getForeignKeys() as $foreignKey) {
            $tableDependencies[] = $foreignKey->getForeignTableName();
        }

        return $tableDependencies;
    }

    protected function replaceMethodBody($methodName, $code, $migrationContents)
    {
        return preg_replace(
            '/(function\\s+' . $methodName . '\\(\\)\\s*{)\\s*.*(\\s*})/',
            '$1' . PHP_EOL . $code . '$2',
            $migrationContents
        );
    }

    protected function createMigrationCode(SchemaDiff $diff)
    {
        $code = new PhpBuilder();

        $code->indent = 2;

        foreach ($diff->changedTables as $table) {
            $oldName = var_export($table->name, true);

            if ($table->newName) {
                $newName = var_export($table->newName, true);

                $code->appendLine("Schema::rename({$oldName}, {$newName});");
            } else {
                $newName = $oldName;
            }

            $code->appendLine("Schema::table({$newName}, function (Blueprint \$table) {");
            $code->indent++;

            foreach ($table->addedColumns as $column) {
                $code->appendLine($this->createAddColumnCode($column));
            }

            foreach ($table->removedColumns as $column) {
                $code->appendLine($this->createDropColumnCode($column));
            }

            foreach ($table->renamedColumns as $oldName => $column) {
                $code->appendLine($this->createRenameColumnCode($oldName, $column->getName()));
            }

            foreach ($table->changedColumns as $column) {
                $code->appendLine($this->createModifyColumnCode($column->oldColumnName, $column->column));
            }

            foreach ($table->addedIndexes as $index) {
                $code->appendLine($this->createAddIndexCode($index));
            }

            foreach ($table->removedIndexes as $index) {
                $code->appendLine($this->createDropIndexCode($index));
            }

            foreach ($table->removedIndexes as $oldName => $index) {
                $code->appendLine($this->createDropIndexCode($oldName));
                $code->appendLine($this->createAddIndexCode($index));
            }

            foreach ($table->changedIndexes as $index) {
                $code->appendLine($this->createDropIndexCode($index->getName()));
                $code->appendLine($this->createAddIndexCode($index));
            }

            foreach ($table->addedForeignKeys as $foreignKey) {
                $code->appendLine($this->createAddForeignKeyCode($foreignKey));
            }

            foreach ($table->removedForeignKeys as $foreignKey) {
                $code->appendLine($this->createDropForeignKeyCode($foreignKey->getName()));
            }

            foreach ($table->changedForeignKeys as $foreignKey) {
                $code->appendLine($this->createDropForeignKeyCode($foreignKey->getName()));
                $code->appendLine($this->createAddForeignKeyCode($foreignKey));
            }

            $code->indent--;
            $code->appendLine('});');
            $code->appendLine();
        }

        foreach ($diff->newTables as $table) {
            $this->appendCreateTableCode($table, $code);
            $code->appendLine();
        }

        foreach ($diff->removedTables as $table) {
            $tableName = var_export($table->getName(), true);

            $code->appendLine("Schema::drop({$tableName});");
        }

        return $code->getCode();
    }

    protected function appendCreateTableCode(Table $table, PhpBuilder $code)
    {
        $tableName = var_export($table->getName(), true);
        $code->appendLine("Schema::create({$tableName}, function (Blueprint \$table) {");
        $code->indent++;
        $hasAutoIncrement = false;

        foreach ($table->getColumns() as $column) {
            $code->appendLine($this->createAddColumnCode($column, false, $hasAutoIncrement));
        }

        $code->appendLine();
        $addedIndex = false;

        foreach ($table->getIndexes() as $index) {
            if ($hasAutoIncrement && $index->isPrimary()) {
                continue;
            }

            $code->appendLine($this->createAddIndexCode($index));
            $addedIndex = true;
        }

        if ($addedIndex && $table->getForeignKeys()) {
            $code->appendLine();
        }

        foreach ($table->getForeignKeys() as $foreignKey) {
            $code->appendLine($this->createAddForeignKeyCode($foreignKey));
        }

        $code->indent--;
        $code->appendLine('});');
    }

    private function exportSimpleArrayOrSingle(array $values)
    {
        return count($values) === 1
            ? var_export(reset($values), true)
            : $this->exportSimpleArray($values);
    }

    private function exportSimpleArray(array $values)
    {
        $elements = array_map(function ($i) {
            return var_export($i, true);
        }, $values);

        return '[' . implode(', ', $elements) . ']';
    }

    private function createAddColumnCode(Column $column, $change = false, &$hasAutoIncrement = false)
    {
        $code          = '$table->';
        $type          = $column->getType();
        $name          = var_export($column->getName(), true);
        $ignoreDefault = false;

        if ($type instanceof BaseEnumType) {
            /** @var BaseEnumType $type */
            $options = $this->exportSimpleArray($type->getValues());
            $code .= "enum({$name}, {$options})";
        } else {
            switch ($type->getName()) {
                case Type::BLOB:
                    // TODO: BLOB length
                    $code .= "binary({$name})";
                    break;
                case Type::BOOLEAN:
                    $code .= "boolean({$name})";
                    break;
                case Type::DATE:
                    $code .= "date({$name})";
                    break;
                case Type::DATETIME:
                    if ($column->getDefault() === 'CURRENT_TIMESTAMP') {
                        $code .= "timestamp({$name})";
                        $ignoreDefault = true;
                    } else {
                        $code .= "dateTime({$name})";
                    }
                    break;
                case Type::DECIMAL:
                    $code .= "decimal({$name}, {$column->getPrecision()}, {$column->getScale()})";
                    break;
                case Type::BIGINT:
                    $code .= "bigInteger({$name})";
                    break;
                case Type::INTEGER:
                    $code .= "integer({$name})";
                    break;
                case MediumIntType::MEDIUMINT:
                    $code .= "mediumInteger({$name})";
                    break;
                case Type::SMALLINT:
                    $code .= "smallInteger({$name})";
                    break;
                case TinyIntType::TINYINT:
                    $code .= "tinyInteger({$name})";
                    break;
                case Type::TEXT:
                    if ($column->getLength() <= pow(2, 16) - 1) {
                        $code .= "text({$name})";
                    } elseif ($column->getLength() <= pow(2, 24) - 1) {
                        $code .= "mediumText({$name})";
                    } else {
                        $code .= "longText({$name})";
                    }
                    break;
                case Type::TIME:
                    $code .= "time({$name})";
                    break;
                case Type::STRING:
                    $code .= "string({$name}, {$column->getLength()})";
                    break;
                default:
                    throw InvalidArgumentException::format('Unknown column type: \'%s\'', $column->getType());
            }
        }

        if (!$column->getNotnull()) {
            $code .= '->nullable()';
        }

        if (!$ignoreDefault && $column->getDefault() !== null) {
            $default = var_export($column->getDefault(), true);
            $code .= "->default({$default})";
        }

        if ($column->getAutoincrement()) {
            $hasAutoIncrement = true;
            $code .= '->autoIncrement()';
        }

        if ($column->getUnsigned()) {
            $code .= "->unsigned()";
        }

        if ($change) {
            $code .= "->change()";
        }

        return $code . ';';
    }

    private function createModifyColumnCode($oldColumnName, Column $newColumn)
    {
        $code = '';
        if ($oldColumnName !== $newColumn->getName()) {
            $code .= $this->createRenameColumnCode($oldColumnName, $newColumn->getName()) . PHP_EOL;
        }

        $code .= $this->createAddColumnCode($newColumn, true);

        return $code;
    }

    private function createDropColumnCode(Column $column)
    {
        $name = var_export($column->getName(), true);

        return "\$table->dropColumn({$name});";
    }

    private function createRenameColumnCode($oldName, $newName)
    {
        $oldName = var_export($oldName, true);
        $newName = var_export($newName, true);

        return "\$table->renameColumn({$oldName}, {$newName});";
    }

    private function createAddIndexCode(Index $index, $overrideName = null)
    {
        $code = '$table->';

        if ($index->isPrimary()) {
            $code .= 'primary(';
        } elseif ($index->isUnique()) {
            $code .= 'unique(';
        } else {
            $code .= 'index(';
        }

        $columns = $this->exportSimpleArrayOrSingle($index->getColumns());

        $indexName = $overrideName ?: $index->getName();
        $name      = var_export($indexName, true);

        $code .= $columns . ', ' . $name . ')';

        return $code . ';';
    }

    private function createDropIndexCode($indexName)
    {
        return '$table->dropIndex(' . var_export($indexName, true) . ');';
    }

    private function createAddForeignKeyCode(ForeignKeyConstraint $foreignKey)
    {
        $name              = var_export($foreignKey->getName(), true);
        $localColumns      = $this->exportSimpleArrayOrSingle($foreignKey->getLocalColumns());
        $referencedTable   = var_export($foreignKey->getForeignTableName(), true);
        $referencedColumns = $this->exportSimpleArrayOrSingle($foreignKey->getForeignColumns());
        $onUpdate          = var_export(strtolower($foreignKey->onUpdate()), true);
        $onDelete          = var_export(strtolower($foreignKey->onDelete()), true);

        $indent = PHP_EOL . str_repeat(' ', 8);

        return "\$table->foreign({$localColumns}, {$name})"
        . $indent . "->references({$referencedColumns})"
        . $indent . "->on({$referencedTable})"
        . $indent . "->onDelete({$onDelete})"
        . $indent . "->onUpdate({$onUpdate})"
        . ";";
    }

    private function createDropForeignKeyCode($foreignKeyName)
    {
        return '$table->dropForeign(' . var_export($foreignKeyName, true) . ');';
    }
}