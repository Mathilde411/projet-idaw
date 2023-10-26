<?php

namespace App\Database;

use App\Config\Config;
use App\Database\Connection\DbConnection;
use App\Database\Connection\MySQLConnection;

class DatabaseManager
{
    private ?DbConnection $connection = null;

    public function __construct(protected Config $config)
    {
    }

    /**
     * @throws DatabaseException
     */
    private function setupConnection(): void
    {
        $connection = match ($this->config->get('database.type')) {
            'mysql' => new MySQLConnection(),
            default => throw new DatabaseException("Le type de base de données n'est pas supporté."),
        };

        $connection->connect($this->config->get('database'));

        $this->connection = $connection;
    }

    /**
     * @throws DatabaseException
     */
    public function connection(): DbConnection
    {
        if (!isset($this->connection))
            $this->setupConnection();

        return $this->connection;
    }
}