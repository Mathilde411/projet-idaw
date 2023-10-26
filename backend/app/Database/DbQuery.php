<?php

namespace App\Database;

use PDO;
use PDOException;
use PDOStatement;

class DbQuery
{

    private string $lastId;

    public function __construct(private readonly PDOStatement $statement, private readonly PDO $connection)
    {}

    /**
     * @throws DatabaseException
     */
    public function execute(array $param = []) : static {
        try {
            $this->statement->execute($param);
            $this->lastId = $this->connection->lastInsertId();
        }
        catch (PDOException $error) {
            throw new DatabaseException($error->getMessage(), $error->getCode());
        }
        return $this;
    }

    public function getUpdatedRows() : int {
        return $this->statement->rowCount();
    }

    public function getLastId(): string
    {
        return $this->lastId;
    }

    public function next() : ?array {
        if(!($res = $this->statement->fetch(PDO::FETCH_ASSOC)))
            return null;
        return $res;
    }

    public function all() : array {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }
}