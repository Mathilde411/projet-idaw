<?php

namespace App\Model\Relationships;

use App\Database\DbQueryBuilder;
use App\Facade\DB;
use App\Model\Relationships\Relationship;

class BelongsToOne extends Relationship
{
    // Instance
    private mixed $instance;

    // Classes
    private string $selfClass;
    private string $relationClass;

    // Tables
    private string $selfTable;
    private string $relationTable;

    // Columns
    private string $selfForeignKey;
    private string $relationPrimaryKey;

    public function __construct(mixed $instance, string $selfClass, string $relationClass, string $selfForeignKey, string $relationPrimaryKey)
    {
        $this->instance = $instance;
        $this->selfClass = $selfClass;
        $this->relationClass = $relationClass;
        $this->selfTable = $selfClass::getTable();
        $this->relationTable = $relationClass::getTable();
        $this->selfForeignKey = $selfForeignKey;
        $this->relationPrimaryKey = $relationPrimaryKey;
    }

    protected function prepareBaseQuery(): DbQueryBuilder
    {
        $pk = ($this->selfClass)::getPrimaryKey();
        return DB::table(DB::raw($this->selfTable . ' AS left_table'))
            ->where('left_table.' . $pk, $this->instance->$pk)
            ->join(DB::raw($this->relationTable . ' AS right_table'), 'left_table.' . $this->selfForeignKey , '=', 'right_table.' . $this->relationPrimaryKey)
            ->select('right_table.*')
            ->wrapper($this->relationClass, true);
    }

    public function execute(): mixed
    {
        return $this->prepareBaseQuery()->first();
    }
}