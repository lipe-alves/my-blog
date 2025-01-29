<?php

namespace App\Core;

class DatabaseConnection
{
    private \PDO|null $conn;
    private \PDOStatement|null $stmt;
    private const DATA_TYPES_X_BIND_TYPES = [
        "boolean" => \PDO::PARAM_BOOL,
        "integer" => \PDO::PARAM_INT,
        "double"  => \PDO::PARAM_INT,
        "string"  => \PDO::PARAM_STR,
        "NULL"    => \PDO::PARAM_NULL
    ];

    protected function __construct()
    {
        $this->conn = null;
        $this->stmt = null;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function execute(string $sql, array $data = null): bool
    {
        $this->stmt = $this->conn->prepare($sql);

        if ($data !== null) {
            foreach ($data as $key => $value) {
                if (!str_contains($sql, ":$key")) continue;

                $data_type = gettype($value);
                $bind_type = self::DATA_TYPES_X_BIND_TYPES[$data_type];
                $this->stmt->bindValue($key, $value, $bind_type);
            }
        }

        return $this->stmt->execute();
    }

    public function select(string $sql, array|null $data = null, int $mode = \PDO::FETCH_DEFAULT): array
    {
        $this->execute($sql, $data);
        return $this->stmt->fetchAll($mode);
    }

    public function selectAll(string $sql, array|null $data = null, int $mode = \PDO::FETCH_DEFAULT): array
    {
        $this->execute($sql, $data);
        return $this->stmt->fetchAll($mode);
    }

    public function selectOne(string $sql, array|null $data = null, int $mode = \PDO::FETCH_DEFAULT): array
    {
        $this->execute($sql, $data);
        return $this->stmt->fetch($mode);
    }

    public function update(string $sql, array|null $data = null): bool
    {
        if (!preg_match("/^UPDATE/i", $sql)) return false;
        return $this->execute($sql, $data);
    }

    public function insert(string $sql, array|null $data = null): bool
    {
        if (!preg_match("/^INSERT/i", $sql)) return false;
        return $this->execute($sql, $data);
    }

    public function delete(string $sql, array|null $data = null): bool
    {
        if (!preg_match("/^DELETE/i", $sql)) return false;
        return $this->execute($sql, $data);
    }

    public function getLastInsertedId(): string|false
    {
        return $this->conn->lastInsertId();
    }

    public function startTransaction(): bool
    {
        return $this->conn->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->conn->commit();
    }

    public function rollback(): bool
    {
        return $this->conn->rollback();
    }

    public function connect(): self
    {
        $this->conn = new \PDO(
            "mysql:host=$_ENV[DB_HOST];port=$_ENV[DB_PORT];dbname=$_ENV[DB_NAME]",
            $_ENV["DB_USER"],
            $_ENV["DB_PASSWORD"]
        );

        $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $this;
    }

    public function disconnect(): self
    {
        $this->conn = null;
        return $this;
    }

    public static function create(): DatabaseConnection
    {
        return (new DatabaseConnection())->connect();
    }
}
