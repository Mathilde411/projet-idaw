<?php

namespace App\Database;

use App\Model\Model;
use Closure;
use PDO;
use TypeError;

class DbQueryBuilder implements QueryProvider
{

    private string $table;

    private array $where = [];
    private array $having = [];
    private array $groupBy = [];
    private array $orderBy = [];
    private array $select = ['*'];
    private array $join = [];

    private ?int $limit = null;
    private ?int $offset = null;

    private array $insert = [];

    private array $param = [];
    private array $update = [];

    private ?string $wrapperModel;
    private bool $canWrap = true;


    public function __construct(protected DatabaseManager $db, private int $paramCount = 0)
    {
        array_map(null, []);
    }

    public function table(string|RawSQL|QueryProvider $table): static
    {
        if($table instanceof QueryProvider) {
            $sub = $this->extractSubQuery($table);
            $this->table = $this->fuseSubQuery($sub)->raw . ' table_' . substr(str_shuffle(MD5(microtime())), 0, 4);
            $this->wrapperModel = $sub->getWraper();
        } elseif($table instanceof RawSQL) {
            $this->table = $table->raw;
        } else {
            $this->testSQLIdentifier($table, true);
            $this->table = $table;
        }
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


    public function sqlFunction(string $function, string $identifier, ?string $newName = null): RawSQL
    {
        $this->testSQLIdentifier($identifier, true);
        $sql = $function . '(' . $identifier . ')';
        if (isset($newName)) {
            $this->testSQLIdentifier($newName, true);
            $sql .= ' AS ' . $newName;
        }
        return new RawSQL($sql);
    }

    public function sum(string $identifier, ?string $newName = null): RawSQL
    {
        return $this->sqlFunction('SUM', $identifier, $newName);
    }

    public function min(string $identifier, ?string $newName = null): RawSQL
    {
        return $this->sqlFunction('MIN', $identifier, $newName);
    }

    public function max(string $identifier, ?string $newName = null): RawSQL
    {
        return $this->sqlFunction('MAX', $identifier, $newName);
    }

    public function avg(string $identifier, ?string $newName = null): RawSQL
    {
        return $this->sqlFunction('AVG', $identifier, $newName);
    }

    public function count(string $identifier, ?string $newName = null): RawSQL
    {
        return $this->sqlFunction('COUNT', $identifier, $newName);
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

    public function where(RawSQL|Closure|string|QueryProvider $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseWhere(false, false, $this->parseCondition($var, $op, $value));
    }

    public function orWhere(RawSQL|Closure|string|QueryProvider $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseWhere(true, false, $this->parseCondition($var, $op, $value));
    }

    public function whereNot(RawSQL|Closure|string|QueryProvider $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseWhere(false, true, $this->parseCondition($var, $op, $value));
    }

    public function orWhereNot(RawSQL|Closure|string|QueryProvider $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseWhere(true, true, $this->parseCondition($var, $op, $value));
    }

    public function select(string|RawSQL|array $columns): static
    {
        $this->select = $this->extractColumns($columns);
        $this->canWrap = false;

        return $this;
    }

    public function groupBy(string|RawSQL|array $columns): static
    {

        $this->groupBy = $this->extractColumns($columns);
        $this->canWrap = false;

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

    public function having(RawSQL|Closure|string|QueryProvider $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseHaving(false, false, $this->parseCondition($var, $op, $value));
    }

    public function orHaving(RawSQL|Closure|string|QueryProvider $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseHaving(true, false, $this->parseCondition($var, $op, $value));
    }

    public function havingNot(RawSQL|Closure|string|QueryProvider $var, mixed $op = null, mixed $value = null): static
    {
        return $this->baseHaving(false, true, $this->parseCondition($var, $op, $value));
    }

    public function orHavingNot(RawSQL|Closure|string|QueryProvider $var, mixed $op = null, mixed $value = null): static
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

    private function generalJoin(string $type, string|RawSQL|QueryProvider $table, string|RawSQL $colA, string $op, string|RawSQL $colB): static
    {
        if($table instanceof QueryProvider)
            $table = $this->fuseSubQuery($table);

        if($table instanceof RawSQL)
            $table = $table->raw;
        else
            $this->testSQLIdentifier($table, true);

        $colA = $this->extractColumns($colA)[0];
        $colB = $this->extractColumns($colB)[0];

        $this->canWrap = false;

        $this->join[] = $type . ' JOIN ' . $table . ' ON ' . $colA . ' ' . $op . ' ' . $colB;
        return $this;
    }

    public function join(string|RawSQL|QueryProvider $table, string|RawSQL $colA, string $op, string|RawSQL $colB): static
    {
        return $this->generalJoin('INNER', $table, $colA, $op, $colB);
    }

    public function leftJoin(string|RawSQL|QueryProvider $table, string|RawSQL $colA, string $op, string|RawSQL $colB): static
    {
        return $this->generalJoin('LEFT', $table, $colA, $op, $colB);
    }

    public function rightJoin(string|RawSQL|QueryProvider $table, string|RawSQL $colA, string $op, string|RawSQL $colB): static
    {
        return $this->generalJoin('RIGHT', $table, $colA, $op, $colB);
    }

    public function crossJoin(string|RawSQL|QueryProvider $table, string|RawSQL $colA, string $op, string|RawSQL $colB): static
    {
        return $this->generalJoin('CROSS', $table, $colA, $op, $colB);
    }

    public function wrapper(string $class, bool $forceWrap = false): static
    {
        $this->wrapperModel = $class;
        if($forceWrap)
            $this->canWrap = true;
        return $this;
    }

    private function wrap(?array $data)
    {
        if (isset($data) and isset($this->wrapperModel) and $this->canWrap) {
            /**
             * @var Model $model
             */
            $model = new $this->wrapperModel();
            $model->setAttributes($data, true);
            return $model;
        } else
            return $data;
    }

    public function get(): array
    {
        return array_map(
            function (array $data) {
                return $this->wrap($data);
            },
            $this
                ->db->connection()
                ->query($this->buildSelect())
                ->execute($this->param)
                ->all()
        );
    }

    public function first(): mixed
    {
        return $this->wrap($this
            ->db->connection()
            ->query($this->buildSelect())
            ->execute($this->param)
            ->next());
    }

    public function insert(array $data): int
    {
        $this->prepareInsert($data);
        return $this
            ->db->connection()
            ->query($this->buildInsert())
            ->execute($this->param)
            ->getUpdatedRows();
    }

    public function insertLastId(array $data): int
    {
        $this->prepareInsert($data);
        return $this
            ->db->connection()
            ->query($this->buildInsert())
            ->execute($this->param)
            ->getLastId();
    }

    public function update(array $data): int
    {
        $this->prepareUpdate($data);
        return $this
            ->db->connection()
            ->query($this->buildUpdate())
            ->execute($this->param)
            ->getLastId();
    }

    public function delete(): int
    {
        return $this
            ->db->connection()
            ->query($this->buildDelete())
            ->execute($this->param)
            ->getUpdatedRows();
    }

    //----------------------------------------------------------------------------------------------------------------//

    private function prepareInsert(array $data)
    {
        if (count($data) == 0 or !is_array($data[0]))
            $data = [$data];

        $set = [];
        foreach ($data as $field) {
            foreach (array_keys($field) as $key) {
                $set[$key] = true;
            }
        }

        foreach (array_keys($set) as $key) {
            $this->testSQLIdentifier($key, true);
        }
        $this->insert['keys'] = array_keys($set);
        $this->insert['data'] = [];

        foreach ($data as $field) {
            $obj = [];
            foreach (array_keys($set) as $key) {
                $obj[$key] = ($field[$key] ?? null);
            }
            $this->insert['data'][] = $obj;
        }

    }

    private function prepareUpdate(array $data): void
    {

        foreach (array_keys($data) as $key) {
            $this->testSQLIdentifier($key, true);
        }

        $this->update = $data;

    }

    private function testSQLIdentifier(string $identifier, bool $throw = false, bool $allowDots = true): bool
    {
        if(str_contains($identifier, '.') and $allowDots) {
            foreach (explode('.', $identifier) as $splIdentifier) {
                $this->testSQLIdentifier($splIdentifier, $throw, false);
            }
            return true;
        }

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

    private function parseConditionValue(mixed $value): string
    {
        if($value instanceof QueryProvider)
            return $this->fuseSubQuery($value)->raw;
        return $this->assignParam($value);
    }

    private function parseCondition(QueryProvider|RawSQL|Closure|string $var, mixed $op = null, mixed $value = null): Closure|string
    {
        if ($var instanceof Closure)
            return $var;

        if($var instanceof QueryProvider)
            $var = $this->fuseSubQuery($var);

        if ($var instanceof RawSQL) {
            if (isset($op)) {
                if (isset($value))
                    return $var->raw . ' ' . $op . ' ' . $this->parseConditionValue($value);
                else
                    return $var->raw . ' = ' . $this->parseConditionValue($op);
            } else
                return $var->raw;
        }

        if (isset($op) and $this->testSQLIdentifier($var, true)) {
            if (isset($value))
                return $var . ' ' . $op . ' ' . $this->parseConditionValue($value);
            else
                return $var . ' = ' . $this->parseConditionValue($op);
        }

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

    private function buildJoinAppendix(): string
    {
        if (empty($this->join))
            return '';

        return ' ' . implode(' ', $this->join);
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

    private function buildSelect(bool $ending = true): string
    {
        return 'SELECT '
            . implode(', ', $this->select)
            . ' FROM '
            . $this->table
            . $this->buildJoinAppendix()
            . $this->buildWhereAppendix()
            . $this->buildGroupByAppendix()
            . $this->buildHavingAppendix()
            . $this->buildOrderByAppendix()
            . $this->buildLimit()
            . $this->buildOffset()
            . ($ending ? ';' : '');
    }

    private function buildDelete(): string
    {
        return 'DELETE FROM '
            . $this->table
            . $this->buildWhereAppendix()
            . ';';
    }

    private function buildInsert(): string
    {
        return 'INSERT INTO '
            . $this->table
            . ' (' . implode(', ', $this->insert['keys']) . ')'
            . ' VALUES '
            . implode(', ', array_map(function (array $data) {
                $res = [];
                foreach ($this->insert['keys'] as $key) {
                    $res[] = $this->assignParam($data[$key]);
                }
                return '(' . implode(', ', $res) . ')';
            }, $this->insert['data']))
            . ';';
    }

    private function buildUpdate(): string
    {
        return 'UPDATE '
            . $this->table
            . ' SET '
            . implode(', ', array_map(function (string $key) {
                return $key . ' = ' . $this->assignParam($this->update[$key]);
            }, array_keys($this->update)))
            . $this->buildWhereAppendix()
            . ';';
    }

    public function getQuery() : DbQueryBuilder
    {
        return $this;
    }

    private function fuseSubQuery(SubQuery|QueryProvider $query): RawSQL
    {
        if($query instanceof QueryProvider)
            $query = $this->extractSubQuery($query);

        $this->paramCount = $query->reIndex($this->paramCount);
        $this->param = array_merge($this->param, $query->getParam());
        return $query->getRaw();
    }

    private function extractSubQuery(QueryProvider $provider): SubQuery
    {
        $query = $provider->getQuery();
        return new SubQuery($query->buildSelect(false), $query->param, ($query->canWrap and isset($query->wrapperModel)) ? $query->wrapperModel:null);
    }


}

