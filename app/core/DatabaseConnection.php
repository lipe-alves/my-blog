<?php

namespace App\Core;

class DatabaseConnection
{
    private \PDO|null $conn;
    private \PDOStatement|null $stmt;

    protected function __construct()
    {
        $this->conn = null;
        $this->stmt = null;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function select(string $sql, array|null $data = null): array
    {
        $this->stmt = $this->conn->prepare($sql);

        if ($data !== null) {
            foreach ($data as $key => $value) {
                $this->stmt->bindValue(":$key", $value);
            }
        }

        return $this->stmt->fetchAll();
    }

    public function execute(string $sql, array $data = null): bool
    {
        $this->stmt = $this->conn->prepare($sql);
        return $this->stmt->execute($data);
    }

    public function connect(): self
    {
        $this->conn = new \PDO(
            "mysql:host=$_ENV[DB_HOST];dbname=$_ENV[DB_NAME]",
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
