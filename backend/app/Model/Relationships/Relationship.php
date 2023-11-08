<?php

namespace App\Model\Relationships;

use App\Database\QueryProvider;
use App\Facade\DB;
use App\Model\Model;

abstract class Relationship implements QueryProvider
{
    public abstract function execute() : mixed;

    public function __call(string $name, array $arguments)
    {
        return DB::table($this->getQuery())->$name(...$arguments);
    }

    public abstract function link(Model $relationInstance, array $arguments = []);
}