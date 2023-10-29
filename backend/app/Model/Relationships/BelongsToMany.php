<?php

namespace App\Model\Relationships;

use App\Database\DbQueryBuilder;
use App\Facade\DB;

class BelongsToMany extends Relationship
{
// Instance
    private mixed $instance;

    // Classes
    private string $selfClass;
    private string $relationClass;

    // Tables
    private string $selfTable;
    private string $relationTable;
    private string $middleTable;

    // Columns
    private string $selfPrimaryKey;
    private string $relationPrimaryKey;

    private string $middleSelfForeignKey;
    private string $middleRelationForeignKey;

    public function __construct(mixed $instance, string $selfClass, string $relationClass, string $middleTable, string $selfPrimaryKey, string $relationPrimaryKey, string $middleSelfForeignKey, string $middleRelationForeignKey)
    {
        $this->instance = $instance;
        $this->selfClass = $selfClass;
        $this->relationClass = $relationClass;
        $this->selfTable = $selfClass::getTable();
        $this->relationTable = $relationClass::getTable();
        $this->middleTable = $middleTable;
        $this->selfPrimaryKey = $selfPrimaryKey;
        $this->relationPrimaryKey = $relationPrimaryKey;
        $this->middleSelfForeignKey = $middleSelfForeignKey;
        $this->middleRelationForeignKey = $middleRelationForeignKey;
    }

    protected function prepareBaseQuery(): DbQueryBuilder
    {
        $pk = ($this->selfClass)::getPrimaryKey();
        return DB::table(DB::raw($this->selfTable . ' AS left_table'))
            ->where('left_table.' . $pk, $this->instance->$pk)
            ->join(DB::raw($this->middleTable . ' AS middle_table'), 'left_table.' . $this->selfPrimaryKey, '=', 'middle_table.' . $this->middleSelfForeignKey)
            ->join(DB::raw($this->relationTable . ' AS right_table'), 'middle_table.' . $this->middleRelationForeignKey, '=', 'right_table.' . $this->relationPrimaryKey)
            ->select('right_table.*')
            ->wrapper($this->relationClass, true);
    }

    public function execute(): mixed
    {
        return $this->prepareBaseQuery()->get();
    }
}