<?php

namespace App\Model\Relationships;

use App\Database\DbQueryBuilder;
use App\Facade\DB;
use App\Model\Model;

class Pivot
{
// Instance
    private Model $selfInstance;
    private Model $relationInstance;

    // Classes
    private string $selfClass;
    private string $relationClass;

    // Tables
    private string $middleTable;

    // Columns

    private string $middleSelfForeignKey;
    private string $middleRelationForeignKey;
    private string $selfPrimaryKey;
    private string $relationPrimaryKey;

    public function __construct(Model $selfInstance, Model $relationInstance, string $selfClass, string $relationClass, string $middleTable, string $selfPrimaryKey, string $relationPrimaryKey, string $middleSelfForeignKey, string $middleRelationForeignKey)
    {
        $this->selfInstance = $selfInstance;
        $this->relationInstance = $relationInstance;
        $this->selfClass = $selfClass;
        $this->relationClass = $relationClass;
        $this->middleTable = $middleTable;
        $this->selfPrimaryKey = $selfPrimaryKey;
        $this->relationPrimaryKey = $relationPrimaryKey;
        $this->middleSelfForeignKey = $middleSelfForeignKey;
        $this->middleRelationForeignKey = $middleRelationForeignKey;
    }

    public function getQuery(): DbQueryBuilder
    {
        $selfPk = $this->selfPrimaryKey;
        $relationPk = $this->relationPrimaryKey;
        return DB::table($this->middleTable)
            ->where('middle_table.' . $this->middleSelfForeignKey, $this->selfInstance->$selfPk)
            ->where('middle_table.' . $this->middleRelationForeignKey, $this->relationInstance->$relationPk);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->getQuery()->$name(...$arguments);
    }
}