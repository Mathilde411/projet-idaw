<?php

namespace App\Database;

use PDO;

abstract class DbConnection
{
    public ?PDO $connection = null;

    public function __construct()
    {}

    public abstract function connect(array $config) : bool;
    public function disconnect(): void
    {
        $this->connection = null;
    }
}