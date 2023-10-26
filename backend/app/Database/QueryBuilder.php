<?php

namespace App\Database;

use Closure;
use PDO;
use TypeError;

class QueryBuilder
{

    private string $table;

    private array $where = [];

    private array $param = [];

    public function __construct(protected DatabaseManager $db, private int $paramCount = 0)
    {
    }

    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    private function baseWhereRaw(bool $or, Closure|string $arg): static
    {
        if(!is_string($arg)) {
            $qb = $arg(new QueryBuilder($this->db, $this->paramCount));
            $res = $qb->where;
            $this->paramCount = $qb->paramCount;
            $this->param = array_merge($this->param, $qb->param);
            $arg = empty($res) ? '1' : $res;
        }

        $this->where[] = [
            'or' => $or,
            'arg' => $arg
        ];
        return $this;
    }

    public function whereRaw(Closure|string $arg): static
    {
        return $this->baseWhereRaw(false, $arg);
    }

    public function orWhereRaw(Closure|string $arg): static
    {
        return $this->baseWhereRaw(true, $arg);
    }

    public function where(Closure|string $var, ?string $op = null, mixed $value = null): static
    {
        if($var instanceof Closure)
            return $this->whereRaw($var);
        elseif (isset($op))
            return $this->whereRaw($var . ' ' . $op . ' ' . $this->assignParam($value));
        else
            throw new TypeError("where receives either a Closure or 2 strings and a value.");
    }

    public function orWhere(Closure|string $var, ?string $op = null, mixed $value = null): static
    {
        if($var instanceof Closure)
            return $this->orWhereRaw($var);
        elseif (isset($op))
            return $this->orWhereRaw($var . ' ' . $op . ' ' . $this->assignParam($value));
        else
            throw new TypeError("orWhere receives either a Closure or 2 strings and a value.");
    }

    public function get(array $variables = []) : array {
        $statement = $this
            ->db->connection()
            ->connection->prepare($this->buildSelect($variables));

        $statement->execute($this->param);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(array $variables = []) : array {
        $statement = $this
            ->db->connection()
            ->connection->prepare($this->buildSelect($variables));

        $statement->execute($this->param);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    //----------------------------------------------------------------------------------------------------------------//

    private function assignParam(mixed $value) {
        $key = 'param_' . $this->paramCount;
        $this->param[$key] = $value;
        $this->paramCount++;
        return ':' . $key;
    }

    private function buildCondition(array $conditions): string
    {
        $res = '';

        $first = true;

        foreach ($conditions as $condition) {
            if(!$first)
                $res .= ($condition['or'] ? ' OR ' : ' AND ');

            if(is_string($condition['arg'])) {
                $res .= $condition['arg'];
            } else {
                $res .= '(' . $this->buildCondition($condition['arg']) . ')';
            }

            $first = false;
        }

        return $res;
    }

    private function buildWhereAppendix() : string {
        if(empty($this->where))
            return '';

        return ' WHERE ' . $this->buildCondition($this->where);
    }

    private function buildSelect(array $variables = []) : string {
        if(empty($variables))
            $variables[] = '*';

        return 'SELECT ' . implode(', ', $variables) . ' FROM ' . $this->table . $this->buildWhereAppendix() . ';';
    }
}