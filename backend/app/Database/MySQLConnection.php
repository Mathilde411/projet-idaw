<?php

namespace App\Database;

use PDO;
use PDOException;

class MySQLConnection extends DbConnection
{

    public function connect(array $config): bool
    {
        $connectionString = "mysql:host=". $config['host'];
        if(isset($config['port']))
            $connectionString .= ";port=". $config['port'];
        $connectionString .= ";dbname=" . $config['database'];

        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
        $pdo = NULL;
        try {
            $pdo = new PDO($connectionString, $config['user'], $config['password'], $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $error) {
            return false;
        }
        $this->connection = $pdo;
        return true;
    }
}