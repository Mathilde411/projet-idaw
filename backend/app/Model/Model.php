<?php

namespace App\Model;


use App\Database\Connection\DbConnection;
use App\Database\DbQueryBuilder;
use App\Facade\DB;
use JsonSerializable;
use PDO;

class Model implements JsonSerializable
{
    protected static string $primaryKey = 'id';

    protected static array $publicAttributes = [];
    protected static string $table;

    public static function find(mixed $id): ?static
    {
        return static::where(static::$primaryKey, $id)->first();
    }

    public static function create(array $data): ?static
    {
        $model = new static();

        $model->setAttributes($data);

        $model->insert();

        return $model;
    }

    public static function all()
    {
        return static::get();
    }

    private array $data = [];

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

    public function setAttributes(array $data, bool $sync = false)
    {
        foreach ($data as $key => $val) {
            $this->data[$key] = [
                'val' => $val,
                'sync' => $sync
            ];
        }
    }

    public function fetch(): void
    {
        $pk = static::$primaryKey;
        $this->setAttributes(DB::table(static::$table)->where($pk, $this->$pk)->first(), true);
    }

    public function insert(): void
    {
        $data = array_map(function ($o) {
            return $o['val'];
        }, $this->data);
        $pk = DB::table(static::$table)->insertLastId($data);

        if ($pk != 0)
            $this->data[static::$primaryKey] = [
                'val' => $pk,
                'sync' => true
            ];

        $this->fetch();
    }

    public function update(): void
    {
        $data = array_map(function ($o) {
            return $o['val'];
        }, array_filter($this->data, function ($val) {
            return !$val['sync'];
        }));

        if (count($data) == 0)
            return;

        DB::table(static::$table)->update($data);

        $this->fetch();
    }

    public function delete(): void
    {
        $pk = static::$primaryKey;
        DB::table(static::$table)->where($pk, $this->$pk)->delete();
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        return DB::table(static::$table)->wrapper(static::class)->$name(...$arguments);
    }
}