<?php

namespace App\Model\Relationships;

use App\Database\DbQueryBuilder;
use App\Facade\DB;

class HasMany extends HasOne
{
    public function execute(): array
    {
        return $this->getQuery()->get();
    }
}