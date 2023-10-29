<?php

namespace App\Model\Relationships;

use App\Database\DbQueryBuilder;

abstract class Relationship
{
    protected abstract function prepareBaseQuery() : DbQueryBuilder;

    public abstract function execute() : mixed;
}