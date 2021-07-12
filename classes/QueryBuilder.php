<?php

class QueryBuilder {
    private $sql;

    public function select(array $columns, string $prefix = ''): QueryBuilder
    {
        if ($prefix) {
            array_walk($columns, 'addPrefix', $prefix);
        }

        $columns = implode(', ', $columns);
        $this->sql .= "SELECT $columns";

        return $this;
    }

    public function addSelect(array $columns, string $prefix = ''): QueryBuilder
    {
        if ($prefix) {
            array_walk($columns, 'addPrefix', $prefix);
        }

        $columns = implode(', ', $columns);
        $this->sql .= ", $columns";

        return $this;
    }

    public function from(string $table): QueryBuilder
    {
        $this->sql .= "\nFROM $table";

        return $this;
    }

    public function join(string $type, string $table, string $condition): QueryBuilder
    {
        $this->sql .= "\n$type JOIN $table ON $condition";

        return $this;
    }

    public function innerJoin(string $table, string $condition): QueryBuilder
    {
        $this->sql .= "\nINNER JOIN $table ON $condition";

        return $this;
    }

    public function leftJoin(string $table, string $condition): QueryBuilder
    {
        $this->sql .= "\nLEFT JOIN $table ON $condition";

        return $this;
    }

    public function insert(string $table, array $columns, array $values): QueryBuilder
    {
        $columns = implode(', ', $columns);
        $values = implode(', ', $values);
        $this->sql .= "INSERT INTO $table ($columns) VALUES ($values)";

        return $this;
    }

    public function update(string $table, array $columns): QueryBuilder
    {
        $columns2 = [];
        foreach ($columns as $column => $value) {
            $columns2[] = "$column = $value";
        }

        $columns2 = implode(', ', $columns2);
        $this->sql .= "UPDATE $table SET $columns2";

        return $this;
    }

    public function delete(string $table): QueryBuilder
    {
        $this->sql .= "DELETE FROM $table";

        return $this;
    }

    public function where(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= "\nWHERE $operand1 $operator $operand2";

        return $this;
    }

    public function andWhere(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= " AND $operand1 $operator $operand2";

        return $this;
    }

    public function orWhere(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= " OR $operand1 $operator $operand2";

        return $this;
    }

    public function filterWhere(string $value, string $condition): QueryBuilder
    {
        $value && $this->sql .= "\nWHERE $condition";

        return $this;
    }

    public function andFilterWhere(string $value, string $condition): QueryBuilder
    {
        $value && $this->sql .= " AND $condition";

        return $this;
    }

    public function orderBy(string $column): QueryBuilder
    {
        $this->sql .= "\nORDER BY $column";

        return $this;
    }

    public function groupBy(string $column): QueryBuilder
    {
        $this->sql .= "\nGROUP BY $column";

        return $this;
    }

    public function having(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= "\nHAVING $operand1 $operator $operand2";

        return $this;
    }

    public function orHaving(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= " OR $operand1 $operator $operand2";

        return $this;
    }

    public function limit(string $limit): QueryBuilder
    {
        $this->sql .= " LIMIT $limit";

        return $this;
    }

    public function filterLimit(int $value, string $limit): QueryBuilder
    {
        $value > 0 && $this->sql .= " LIMIT $limit";

        return $this;
    }

    public function offset(string $offset): QueryBuilder
    {
        $this->sql .= " OFFSET $offset";

        return $this;
    }

    public function getQuery(): string
    {
        $query = $this->sql;
        $this->sql = '';

        return $query;
    }

    public function all(array $stmt_data = []): array
    {
        return Database::getInstance()->select($this->getQuery(), $stmt_data);
    }

    public function one(array $stmt_data = [])
    {
        return Database::getInstance()->selectOne($this->getQuery(), $stmt_data);
    }

    public function exists(array $stmt_data = []): bool
    {
        return boolval(Database::getInstance()->getNumRows($this->getQuery(), $stmt_data));
    }
}
