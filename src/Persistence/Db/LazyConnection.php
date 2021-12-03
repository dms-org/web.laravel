<?php

namespace Dms\Web\Laravel\Persistence\Db;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Connection\IQuery;
use Dms\Core\Persistence\Db\Platform\IPlatform;
use Dms\Core\Persistence\Db\Query\BulkUpdate;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\RowSet;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LazyConnection implements IConnection
{
    /**
     * @var callable
     */
    private $factory;

    /**
     * @var IConnection
     */
    private $connection;

    public function __construct(callable $factory)
    {
        $this->factory = $factory; 
    }
    
    private function connection(): IConnection
    {
        if (!$this->connection) {
            $this->connection = ($this->factory)();
        }

        return $this->connection;
    }

    /**
     * Gets the last insert id.
     *
     * @return int
     */
    public function getLastInsertId() : int
    {
        return $this->connection()->getLastInsertId();
    }

    /**
     * Begins a transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        return $this->connection()->beginTransaction();
    }

    /**
     * Returns whether the connection is in a transaction.
     *
     * @return bool
     */
    public function isInTransaction() : bool
    {
        return $this->connection()->isInTransaction();
    }

    /**
     * Commits the transaction.
     *
     * @return void
     */
    public function commitTransaction()
    {
        return $this->connection()->commitTransaction();
    }

    /**
     * Rollsback the transaction.
     *
     * @return void
     */
    public function rollbackTransaction()
    {
        return $this->connection()->rollbackTransaction();
    }

    /**
     * Creates a query with the specified sql and parameters.
     *
     * @param string $sql
     * @param array  $parameters
     *
     * @return IQuery
     */
    public function prepare($sql, array $parameters = []) : IQuery
    {
        return $this->connection()->prepare($sql, $parameters);
    }

    public function getPlatform() : IPlatform
    {
        return $this->connection()->getPlatform();
    }

    /**
     * @param callable $operation
     *
     * @return mixed
     * @throws \Exception
     */
    public function withinTransaction(callable $operation)
    {
        return $this->connection()->withinTransaction($operation);
    }

    /**
     *{@inheritDoc}
     */
    public function load(Select $query) : RowSet
    {
        return $this->connection()->load($query);
    }

    /**
     *{@inheritDoc}
     */
    public function update(Update $query) : int
    {
        return $this->connection()->update($query);
    }

    /**
     *{@inheritDoc}
     */
    public function delete(Delete $query) : int
    {
        return $this->connection()->delete($query);
    }

    /**
     *{@inheritDoc}
     */
    public function upsert(Upsert $query)
    {
        return $this->connection()->upsert($query);
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdate(BulkUpdate $query)
    {
        return $this->connection()->bulkUpdate($query);
    }

    /**
     * @inheritDoc
     */
    public function resequenceOrderIndexColumn(ResequenceOrderIndexColumn $query)
    {
        return $this->connection()->resequenceOrderIndexColumn($query);
    }
}