<?php

namespace App\Database\Connection;

use App\Database\DatabaseException;
use App\Database\DbQuery;
use Closure;
use PDO;
use PDOException;

abstract class DbConnection
{
    protected ?PDO $connection = null;

    /**
     * @throws DatabaseException
     */
    public abstract function connect(array $config) : void;

    public function query(string $sql): DbQuery
    {
        return new DbQuery($this->connection->prepare($sql), $this->connection);
    }

    /**
     * @throws DatabaseException
     */
    public function runTransaction(Closure $calls): bool
    {
        try {
            $this->connection->beginTransaction();
            $res = $calls();

            if(!isset($res) or $res) {
                $this->connection->commit();
                return true;
            } else {
                $this->connection->rollBack();
                return false;
            }
        } catch (PDOException $error) {
            throw new DatabaseException($error->getMessage(), $error->getCode());
        } finally {
            if($this->connection->inTransaction())
                $this->connection->rollBack();
        }

    }
}