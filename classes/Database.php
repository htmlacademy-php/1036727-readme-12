<?php

class Database {
    private static $mysqli;

    public static function getInstance(string $host, string $username, string $passwd, string $dbname): mysqli
    {
        if (self::$mysqli === null) {
            self::$mysqli = new mysqli($host, $username, $passwd, $dbname);

            if (self::$mysqli->connect_error) {
                http_response_code(500);
                exit;
            }
        }

        return self::$mysqli;
    }

    private function __constructor() {}
    private function __clone() {}
    private function __wakeup() {}

    public function select(string $sql, array $stmt_data): array
    {
        $stmt = $this->executeQuery(string $sql, array $stmt_data);
        if (!$result = $stmt->get_result()) {
            http_response_code(500);
            exit;
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function selectOne(string $sql, array $stmt_data): array
    {
        $stmt = $this->executeQuery(string $sql, array $stmt_data);
        if (!$result = $stmt->get_result()) {
            http_response_code(500);
            exit;
        }

        return $result->fetch_assoc();
    }

    public function executeQuery(string $sql, array $stmt_data): mysqli_stmt
    {
        $stmt = $this->getPrepareStmt($sql, $types, $stmt_data);
        if (!$stmt->execute()) {
            http_response_code(500);
            exit;
        }

        return $stmt;
    }

    private function getPrepareStmt(string $sql, array $data = []): mysqli_stmt
    {
        if (!$stmt = self::$mysqli->prepare($sql)) {
            http_response_code(500);
            exit;
        }

        if ($data) {
            $types = '';
            $stmt_data = [];

            foreach ($data as $value) {
                $type = 's';

                if (is_int($value)) {
                    $type = 'i';
                } elseif (is_double($value)) {
                    $type = 'd';
                } elseif (is_string($value)) {
                    $type = 's';
                }

                if ($type) {
                    $types .= $type;
                    $stmt_data[] = $value;
                }
            }

            $values = array_merge([$types], $stmt_data);

            if (!$stmt->bind_param(...$values)) {
                http_response_code(500);
                exit;
            }
        }

        return $stmt;
    }
}
