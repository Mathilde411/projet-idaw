<?php

namespace App\Model;


use App\Facade\DB;
use App\Model\Relationships\BelongsToMany;
use App\Model\Relationships\BelongsToOne;
use App\Model\Relationships\HasMany;
use App\Model\Relationships\HasOne;
use App\Model\Relationships\Relationship;
use JsonSerializable;

class Model implements JsonSerializable
{
    protected static string $primaryKey = 'id';

    protected static array $publicAttributes = [];
    protected static array $hooks = [];
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

        return array_filter($attr, function ($key) {
            return in_array($key, static::$publicAttributes);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function hasOne(string $relationClass, string $foreignKey, ?string $localKey = null) : HasOne{
        if(!isset($localKey))
            $localKey = static::$primaryKey;

        return new HasOne($this, static::class, $relationClass, $localKey, $foreignKey);
    }

    protected function hasMany(string $relationClass, string $foreignKey, ?string $localKey = null) : HasMany{
        if(!isset($localKey))
            $localKey = static::$primaryKey;

        return new HasMany($this, static::class, $relationClass, $localKey, $foreignKey);
    }

    protected function belongsTo(string $relationClass, string $foreignKey, ?string $localKey = null) : BelongsToOne {
        if(!isset($localKey))
            $localKey = $relationClass::$primaryKey;

        return new BelongsToOne($this, static::class, $relationClass, $foreignKey, $localKey);
    }

    protected function belongsToMany(string $relationClass, string $middleTable, string $selfForeignKey, string $relationForeignKey, ?string $selfLocalKey = null, ?string $relationLocalKey = null) : BelongsToMany
    {
        if(!isset($relationLocalKey))
            $relationLocalKey = $relationClass::$primaryKey;

        if(!isset($selfLocalKey))
            $selfLocalKey = static::$primaryKey;

        return new BelongsToMany($this, static::class, $relationClass, $middleTable, $selfLocalKey, $relationLocalKey, $selfForeignKey, $relationForeignKey);
    }

    public function __get(string $name)
    {
        if(isset($this->data[$name])) {
            $val = $this->data[$name]['val'];
            if(isset(static::$hooks) and isset(static::$hooks['get'])) {
                return static::$hooks['get']($val);
            } else {
                return $val;
            }
        } elseif(method_exists($this, $name) and (($rel = $this->$name()) instanceof Relationship)) {
            return $rel->execute();
        }
        return null;
    }

    public function __set(string $name, $value): void
    {
        $this->data[$name] = [
            'val' => (isset(static::$hooks) and isset(static::$hooks['set'])) ? static::$hooks['set']($value) : $value,
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

    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    public static function getPublicAttributes(): array
    {
        return static::$publicAttributes;
    }

    public static function getTable(): string
    {
        return static::$table;
    }


}