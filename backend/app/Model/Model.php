<?php

namespace App\Model;


use App\Database\DbConnection;
use App\Facade\Database;
use JsonSerializable;
use PDO;

class Model implements JsonSerializable
{
    protected static string $primaryKey = 'id';

    protected static array $publicAttributes = [];
    protected static string $table;

    public static function get(mixed $id): ?static
    {
        $model = new static();
        $model->__set(static::$primaryKey, $id);

        if ($model->fetch())
            return $model;
        return null;
    }

    public static function create(array $data): ?static
    {
        $model = new static();

        $model->setAttributes($data);

        if ($model->insert())
            return $model;
        return null;
    }

    public static function all() {
        $db = Database::connection();
        $sql = static::fetchAllQuery();
        $stmt = $db->connection->prepare($sql);
        $stmt->execute();

        $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach ($ret as $data) {
            $model = new static();
            $model->setAttributes($data, true);
            $res[] = $model;
        }
        return $res;
    }

    private array $data = [];
    private ?DbConnection $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function jsonSerialize(): mixed
    {
        $attr = array_map(function ($o) {
            return $o['val'];
        }, $this->data);

        $attr = array_filter($attr, function ($key) {
            return in_array($key, static::$publicAttributes);
        }, ARRAY_FILTER_USE_KEY);

        return $attr;
    }

    public function __get(string $name)
    {
        return isset($this->data[$name]) ? $this->data[$name]['val'] : null;
    }

    public function __set(string $name, $value): void
    {
        $this->data[$name] = [
            'val' => $value,
            'sync' => false
        ];
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function setAttributes(array $data, bool $sync = false) {
        foreach ($data as $key => $val) {
            $this->data[$key] = [
                'val' => $val,
                'sync' => $sync
            ];
        }
    }

    public function fetch(): bool
    {
        $sql = $this->fetchQuery();
        $stmt = $this->db->connection->prepare($sql);
        $stmt->execute([static::$primaryKey => $this->data[static::$primaryKey]['val']]);

        if ($stmt->rowCount() == 0)
            return false;

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        foreach ($res as $attr => $val) {
            $this->data[$attr] = [
                'val' => $val,
                'sync' => true
            ];
        }
        return true;
    }

    public function insert(): bool
    {
        $sql = $this->insertQuery();
        $stmt = $this->db->connection->prepare($sql);
        if (!$stmt->execute(array_map(function ($o) {
            return $o['val'];
        }, $this->data)))
            return false;

        if (($lastId = $this->db->connection->lastInsertId()) != 0)
            $this->__set(static::$primaryKey, $lastId);

        foreach ($this->data as $attr => $val) {
            $this->data[$attr]['sync'] = true;
        }

        return true;
    }

    public function update(): bool
    {
        $toUpdate = array_filter($this->data, function ($val) { return !$val['sync'];});

        if(count($toUpdate) == 0)
            return true;

        $sql = $this->updateQuery(array_keys($toUpdate));
        $stmt = $this->db->connection->prepare($sql);

        $param = array_map(function ($o) {
            return $o['val'];
        }, $toUpdate);

        $param[static::$primaryKey] = $this->__get(static::$primaryKey);

        if (!$stmt->execute($param))
            return false;

        foreach ($toUpdate as $attr => $val) {
            $this->data[$attr]['sync'] = true;
        }

        return true;
    }

    public function delete(): bool
    {
        $sql = $this->deleteQuery();
        $stmt = $this->db->connection->prepare($sql);

        if (!$stmt->execute([static::$primaryKey => $this->data[static::$primaryKey]['val']]))
            return false;

        $this->data = [];

        return true;
    }

    // REQUESTS

    private function fetchQuery(): string
    {
        return "SELECT * FROM " .
            static::$table .
            " WHERE " .
            static::$primaryKey .
            " = :" .
            static::$primaryKey;
    }

    private static function fetchAllQuery(): string
    {
        return "SELECT * FROM " .
            static::$table;
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " .
            static::$table .
            " (" . implode(', ', array_keys($this->data)) . ") " .
            "VALUES (" . implode(', ', array_map(function ($s) {
                return ':' . $s;
            }, array_keys($this->data))) . ");";

    }

    private function updateQuery(array $attributes): string
    {
        return "UPDATE " .
            static::$table . " SET " .
            implode(', ', array_map(function ($s) {
                return $s . ' = :' . $s;
            }, $attributes)) . " WHERE " .
            static::$primaryKey .
            " = :" .
            static::$primaryKey;

    }

    private function deleteQuery(): string
    {
        return "DELETE FROM " .
            static::$table .
            " WHERE " .
            static::$primaryKey .
            " = :" .
            static::$primaryKey;
    }


}