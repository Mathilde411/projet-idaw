<?php

namespace App\Database;

use Closure;
use PDO;
use TypeError;

class DbQueryBuilder
{

    private string $table;

    private array $where = [];
    private array $having = [];
    private array $groupBy = [];
    private array $orderBy = [];
    private array $select = ['*'];

    private ?int $limit = null;
    private ?int $offset = null;

    private array $param = [];


    public function __construct(protected DatabaseManager $db, private int $paramCount = 0)
    {
    }

    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @throws DatabaseException
     */
    public function transaction(Closure $calls): bool
    {
        return $this->db->connection()->runTransaction($calls);
    }

    public function raw(string $raw): RawSQL
    {
        return new RawSQL($raw);
    }

    public function sum(string $identifier): RawSQL
    {
        $this->testSQLIdentifier($identifier, true);
        return new RawSQL('SUM(' . $identifier . ')');
    }

    public function min(string $identifier): RawSQL
    {
        $this->testSQLIdentifier($identifier, true);
        return new RawSQL('MIN(' . $identifier . ')');
    }

    public function max(string $identifier): RawSQL
    {
        $this->testSQLIdentifier($identifier, true);
        return new RawSQL('MAX(' . $identifier . ')');
    }

    public function avg(string $identifier): RawSQL
    {
        $this->testSQLIdentifier($identifier, true);
        return new RawSQL('AVG(' . $identifier . ')');
    }

    public function count(string $identifier): RawSQL
    {
        $this->testSQLIdentifier($identifier, true);
        return new RawSQL('COUNT(' . $identifier . ')');
    }

    private function baseWhere(bool $or, bool $not, Closure|string $arg): static
    {
        $arg = $this->extractCondition($arg);

        $this->where[] = [
            'or' => $or,
            'not' => $not,
            'arg' => $arg
        ];
        return $this;
    }

    public function where(RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseWhere(false, false, $this->parseCondition($var, $op, $value));
    }

    public function orWhere(RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseWhere(true, false, $this->parseCondition($var, $op, $value));
    }

    public function whereNot(RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseWhere(false, true, $this->parseCondition($var, $op, $value));
    }

    public function orWhereNot(RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseWhere(true, true, $this->parseCondition($var, $op, $value));
    }

    public function select(string|RawSQL|array $columns): static
    {
        $this->select = $this->extractColumns($columns);

        return $this;
    }

    public function groupBy(string|RawSQL|array $columns): static
    {

        $this->groupBy = $this->extractColumns($columns);

        return $this;
    }

    private function baseHaving(bool $or, bool $not, Closure|string $arg): static
    {
        $arg = $this->extractCondition($arg);

        $this->having[] = [
            'or' => $or,
            'not' => $not,
            'arg' => $arg
        ];
        return $this;
    }

    public function having(RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseHaving(false, false, $this->parseCondition($var, $op, $value));
    }

    public function orHaving(RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseHaving(true, false, $this->parseCondition($var, $op, $value));
    }

    public function havingNot(RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseHaving(false, true, $this->parseCondition($var, $op, $value));
    }

    public function orHavingNot(RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseHaving(true, true, $this->parseCondition($var, $op, $value));
    }

    public function orderBy(RawSQL|array|string $var, ?bool $asc = null): static
    {
        if (is_array($var) and is_array($var[0])) {
            $transposed = array_map(null, ...$var);
            $this->orderBy = $this->extractColumns($transposed[0]);

            foreach ($this->orderBy as $i => $col) {
                $this->orderBy[$i] .= ($transposed[1][$i] ? ' ASC' : ' DESC');
            }
            return $this;
        }

        $this->orderBy = $this->extractColumns($var);
        if (isset($asc)) {
            $dir = $asc ? ' ASC' : ' DESC';
            foreach ($this->orderBy as $i => $col) {
                $this->orderBy[$i] .= $dir;
            }
        }
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @throws DatabaseException
     */
    public function get(): array
    {
        return $this
            ->db->connection()
            ->query($this->buildSelect())
            ->execute($this->param)
            ->all();
    }

    /**
     * @throws DatabaseException
     */
    public function first(): ?array
    {
        return $this
            ->db->connection()
            ->query($this->buildSelect())
            ->execute($this->param)
            ->next();
    }

    //----------------------------------------------------------------------------------------------------------------//

    private function testSQLIdentifier(string $identifier, bool $throw = false): bool
    {
        if ($identifier == '*')
            return true;

        $res = preg_match('#^([[:alpha:]_][[:alnum:]_]*|("[^"]*")+)$#', $identifier);
        if (!$res and $throw)
            throw new QueryBuildingException($identifier . " is not a valid SQL identifier.");
        return $res;
    }

    private function extractColumns(string|RawSQL|array $columns): array
    {
        if ($columns instanceof RawSQL) {
            return array_map(function (string $part) {
                return trim($part);
            }, explode(',', $columns->raw));
        }

        if (is_string($columns))
            $columns = [$columns];

        foreach ($columns as $i => $column) {
            if ($column instanceof RawSQL)
                $columns[$i] = $column->raw;
            else
                $this->testSQLIdentifier($column, true);
        }

        return $columns;
    }

    private function extractCondition(string|Closure $arg): string
    {
        if (!is_string($arg)) {
            $qb = $arg(new DbQueryBuilder($this->db, $this->paramCount));
            $res = $qb->where;
            $this->paramCount = $qb->paramCount;
            $this->param = array_merge($this->param, $qb->param);
            $arg = empty($res) ? '1' : $res;
        }
        return $arg;
    }

    private function parseCondition(RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): Closure|string
    {
        if ($var instanceof Closure)
            return $var;
        elseif ($var instanceof RawSQL)
            if (isset($op)) {
                if (isset($value))
                    return $var->raw . ' ' . $op . ' ' . $this->assignParam($value);
                else
                    return $var->raw . ' = ' . $this->assignParam($op);
            } else
                return $var->raw;
        elseif (isset($op) and $this->testSQLIdentifier($var, true)) {
            if (isset($value))
                return $var . ' ' . $op . ' ' . $this->assignParam($value);
            else
                return $var . ' = ' . $this->assignParam($op);
        } else
            throw new QueryBuildingException("The condition is not built properly.");
    }

    private function assignParam(mixed $value): string
    {
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
            if (!$first)
                $res .= ($condition['or'] ? ' OR ' : ' AND ');

            $res .= ($condition['not'] ? 'NOT ' : '');
            if (is_string($condition['arg'])) {
                $res .= $condition['arg'];
            } else {
                $res .= '(' . $this->buildCondition($condition['arg']) . ')';
            }

            $first = false;
        }

        return $res;
    }

    private function buildWhereAppendix(): string
    {
        if (empty($this->where))
            return '';

        return ' WHERE ' . $this->buildCondition($this->where);
    }

    private function buildHavingAppendix(): string
    {
        if (empty($this->having))
            return '';

        return ' HAVING ' . $this->buildCondition($this->having);
    }

    private function buildGroupByAppendix(): string
    {
        if (empty($this->groupBy))
            return '';

        return ' GROUP BY ' . implode(', ', $this->groupBy);
    }

    private function buildOrderByAppendix(): string
    {
        if (empty($this->orderBy))
            return '';

        return ' ORDER BY ' . implode(', ', $this->orderBy);
    }

    private function buildLimit(): string
    {
        if (!isset($this->limit))
            return '';

        return ' LIMIT ' . $this->limit;
    }

    private function buildOffset(): string
    {
        if (!isset($this->offset))
            return '';

        return ' OFFSET ' . $this->offset;
    }

    private function buildSelect(array $variables = []): string
    {
        return 'SELECT '
            . implode(', ', $this->select)
            . ' FROM '
            . $this->table
            . $this->buildWhereAppendix()
            . $this->buildGroupByAppendix()
            . $this->buildHavingAppendix()
            . $this->buildOrderByAppendix()
            . $this->buildLimit()
            . $this->buildOffset()
            . ';';
    }
}

