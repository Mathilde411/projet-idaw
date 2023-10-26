<?php

namespace App\Database;

use App\Application;
use App\Facade\Config;

class DatabaseManager
{
    protected ?DbConnection $connection = null;

    public function __construct(protected Application $app)
    {}

    private function setupConnection() : void
    {
        $type = Config::get('database.type');
        if (isset($type)) {
            switch ($type) {
                case 'mysql':
                    $this->connection = new MySQLConnection();
            }
        }

        if (isset($this->connection))
            $this->connection->connect(Config::get('database'));
    }

    public function connection(): DbConnection
    {
        if(!isset($this->connection))
            $this->setupConnection();
        return $this->connection;
    }
}