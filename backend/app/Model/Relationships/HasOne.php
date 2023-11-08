<?php

namespace App\Model\Relationships;

use App\Database\DbQueryBuilder;
use App\Facade\DB;
use App\Model\Model;

class HasOne extends Relationship
{

    // Instance
    private Model $instance;

    // Classes
    private string $selfClass;
    private string $relationClass;

    // Tables
    private string $selfTable;
    private string $relationTable;

    // Columns
    private string $selfPrimaryKey;
    private string $relationForeignKey;

    public function __construct(Model $instance, string $selfClass, string $relationClass, string $selfPrimaryKey, string $relationForeignKey)
    {
        $this->instance = $instance;
        $this->selfClass = $selfClass;
        $this->relationClass = $relationClass;
        $this->selfTable = $selfClass::getTable();
        $this->relationTable = $relationClass::getTable();
        $this->selfPrimaryKey = $selfPrimaryKey;
        $this->relationForeignKey = $relationForeignKey;
    }

    public function getQuery(): DbQueryBuilder
    {
        $pk = ($this->selfClass)::getPrimaryKey();
        return DB::table(DB::raw($this->selfTable . ' AS left_table'))
            ->where('left_table.' . $pk, $this->instance->$pk)
            ->join(DB::raw($this->relationTable . ' AS right_table'), 'left_table.' . $this->selfPrimaryKey , '=', 'right_table.' . $this->relationForeignKey)
            ->select('right_table.*')
            ->wrapper($this->relationClass, true);
    }

    public function execute(): mixed
    {
        return $this->getQuery()->first();
    }

    public function link(Model $relationInstance, array $arguments = [])
    {
        $fk = $this->relationForeignKey;
        $pk = $this->selfPrimaryKey;
        $relationInstance->$fk = $this->instance->$pk;
        $relationInstance->update();
    }
}