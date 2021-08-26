<?php

namespace Anatolev;

class QueryBuilder
{
    private $sql;

    /**
     * Устанавливает фрагмент SELECT SQL запроса.
     * Добавляет префикс таблицы для каждого столбца.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param array $columns Столбцы, которые должны быть выбраны
     * @param string $prefix Префикс таблицы
     *
     * @return QueryBuilder
     */
    public function select(array $columns, string $prefix = ''): QueryBuilder
    {
        if ($prefix) {
            array_walk($columns, 'addPrefix', $prefix);
        }

        $columns = implode(', ', $columns);
        $this->sql .= "SELECT $columns";

        return $this;
    }

    /**
     * Добавляет дополнительные столбцы в часть SQL запроса SELECT.
     * Добавляет префикс таблицы для каждого столбца.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param array $columns Столбцы, которые должны быть выбраны
     * @param string $prefix Префикс таблицы
     *
     * @return QueryBuilder
     */
    public function addSelect(array $columns, string $prefix = ''): QueryBuilder
    {
        if ($prefix) {
            array_walk($columns, 'addPrefix', $prefix);
        }

        $columns = implode(', ', $columns);
        $this->sql .= ", $columns";

        return $this;
    }

    /**
     * Устанавливает фрагмент FROM SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $table
     *
     * @return QueryBuilder
     */
    public function from(string $table): QueryBuilder
    {
        $this->sql .= "\nFROM $table";

        return $this;
    }

    /**
     * Устанавливает фрагмент JOIN SQL запроса.
     * Возвращает экзмепляр класса QueryBuilder
     *
     * @param string $type Тип объединения
     * @param string $table Имя таблицы, которая должна быть присоединена
     * @param string $condition Условие объединения
     *
     * @return QueryBuilder
     */
    public function join(string $type, string $table, string $condition): QueryBuilder
    {
        $this->sql .= "\n$type JOIN $table ON $condition";

        return $this;
    }

    /**
     * Устанавлиает фрагмент INSERT SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     * (количество столбцов и значений должно совпадать)
     *
     * @param string $table
     * @param array $columns
     * @param array $values
     *
     * @return QueryBuilder
     */
    public function insert(string $table, array $columns, array $values): QueryBuilder
    {
        $columns = implode(', ', $columns);
        $values = implode(', ', $values);
        $this->sql .= "INSERT INTO $table ($columns) VALUES ($values)";

        return $this;
    }

    /**
     * Устанавлиает фрагмент UPDATE SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $table Таблица
     * @param array $columns Данные для обновления
     * [столбец-1 => значение-1, столбец-2 => значение-2]
     *
     * @return QueryBuilder
     */
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

    /**
     * Устанавлиает фрагмент DELETE SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $table
     *
     * @return QueryBuilder
     */
    public function delete(string $table): QueryBuilder
    {
        $this->sql .= "DELETE FROM $table";

        return $this;
    }

    /**
     * Устанавливает фрагмент WHERE SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $operator Оператор
     * @param string $operand1 Первый операнд
     * @param string $operand2 Второй операнд
     *
     * @return QueryBuilder
     */
    public function where(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= "\nWHERE $operand1 $operator $operand2";

        return $this;
    }

    /**
     * Добавляет дополнительное условие WHERE в SQL запрос,
     * используя оператор AND (логическое И).
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $operator Оператор
     * @param string $operand1 Первый операнд
     * @param string $operand2 Второй операнд
     *
     * @return QueryBuilder
     */
    public function andWhere(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= " AND $operand1 $operator $operand2";

        return $this;
    }

    /**
     * Добавляет дополнительное условие WHERE в SQL запрос,
     * используя оператор OR (логическое ИЛИ).
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $operator Оператор
     * @param string $operand1 Первый операнд
     * @param string $operand2 Второй операнд
     *
     * @return QueryBuilder
     */
    public function orWhere(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= " OR $operand1 $operator $operand2";

        return $this;
    }

    /**
     * Устанавливает фрагмент WHERE SQL запроса
     * (если $value == true).
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $value Значение для проверки
     * @param string $condition Условие
     *
     * @return QueryBuilder
     */
    public function filterWhere(string $value, string $condition): QueryBuilder
    {
        $value && $this->sql .= "\nWHERE $condition";

        return $this;
    }

    /**
     * Добавляет дополнительное условие WHERE SQL запроса
     * (если $value == true),
     * используя оператор AND (логическое И).
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $value Значение для проверки
     * @param string $condition Условие
     *
     * @return QueryBuilder
     */
    public function andFilterWhere(string $value, string $condition): QueryBuilder
    {
        $value && $this->sql .= " AND $condition";

        return $this;
    }

    /**
     * Устанавливает фрагмент ORDER BY SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $column
     *
     * @return QueryBuilder
     */
    public function orderBy(string $column): QueryBuilder
    {
        $this->sql .= "\nORDER BY $column";

        return $this;
    }

    /**
     * Устанавливает фрагмент GROUP BY SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $column
     *
     * @return QueryBuilder
     */
    public function groupBy(string $column): QueryBuilder
    {
        $this->sql .= "\nGROUP BY $column";

        return $this;
    }

    /**
     * Устанавливает фрагмент HAVING SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $operator Оператор
     * @param string $operand1 Первый операнд
     * @param string $operand2 Второй операнд
     *
     * @return QueryBuilder
     */
    public function having(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= "\nHAVING $operand1 $operator $operand2";

        return $this;
    }

    /**
     * Добавляет дополнительное условие HAVING в SQL запрос,
     * используя оператор OR (логическое ИЛИ).
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $operator Оператор
     * @param string $operand1 Первый операнд
     * @param string $operand2 Второй операнд
     *
     * @return QueryBuilder
     */
    public function orHaving(string $operator, string $operand1, string $operand2): QueryBuilder
    {
        $this->sql .= " OR $operand1 $operator $operand2";

        return $this;
    }

    /**
     * Устанавливает фрагмент LIMIT SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $limit
     *
     * @return QueryBuilder
     */
    public function limit(string $limit): QueryBuilder
    {
        $this->sql .= " LIMIT $limit";

        return $this;
    }

    /**
     * Устанавливает фрагмент LIMIT SQL запроса.
     * (если $value > 0).
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $value Значение для проверки
     * @param string $limit
     *
     * @return QueryBuilder
     */
    public function filterLimit(int $value, string $limit): QueryBuilder
    {
        $value > 0 && $this->sql .= " LIMIT $limit";

        return $this;
    }

    /**
     * Устанавливает фрагмент OFFSET SQL запроса.
     * Возвращает экземпляр класса QueryBuilder
     *
     * @param string $offset
     *
     * @return QueryBuilder
     */
    public function offset(string $offset): QueryBuilder
    {
        $this->sql .= " OFFSET $offset";

        return $this;
    }

    /**
     * Возвращает итоговый SQL запрос.
     * Сбрасывает $this->sql в исходное состояние
     * @return string
     */
    public function getQuery(): string
    {
        $query = $this->sql;
        $this->sql = '';

        return $query;
    }

    /**
     * Возвращает массив строк, каждая из которых
     * это ассоциативный массив пар ключ-значение.
     *
     * @param array $stmt_data Данные для SQL запроса
     * @return array
     */
    public function all(array $stmt_data = []): array
    {
        return Database::getInstance()->select($this->getQuery(), $stmt_data);
    }

    /**
     * Возвращает первую строку запроса или null
     *
     * @param array $stmt_data Данные для SQL запроса
     * @return array
     */
    public function one(array $stmt_data = [])
    {
        return Database::getInstance()->selectOne($this->getQuery(), $stmt_data);
    }

    /**
     * Возвращает значение указывающее, что выборка содержит результат
     *
     * @param array $stmt_data Данные для SQL запроса
     * @return bool
     */
    public function exists(array $stmt_data = []): bool
    {
        return boolval(Database::getInstance()->getNumRows($this->getQuery(), $stmt_data));
    }
}
