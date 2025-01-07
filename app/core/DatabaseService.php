<?php

namespace App\Core;

class DatabaseService
{
    protected const ALIAS_X_CONNECTOR = [
        "&&" => "AND",
        "||" => "OR",
    ];
    protected const ALIAS_X_TABLE_ALIAS = [
        "post"     => "p",
        "category" => "c",
        "comment"  => "comm",
        "reader"   => "r",
    ];
    protected const TABLE_X_ALIAS = [
        "Post"            => "p",
        "Comment"         => "comm",
        "Category"        => "c",
        "Post_x_Category" => "pc",
        "Reader"          => "r",
    ];

    protected DatabaseConnection $conn;

    public function __construct(DatabaseConnection $conn = null)
    {
        if (!$conn) {
            $conn = DatabaseConnection::create();
        }

        $this->conn = $conn;
    }

    private function getTableAlias(string $table_name)
    {
        if (!array_key_exists($table_name, self::TABLE_X_ALIAS)) {
            return strtolower(substr($table_name, 0, 1));
        }

        return self::TABLE_X_ALIAS[$table_name];
    }

    private function convertArrayToWhereCondition(array &$data): string
    {
        $wheres = [];

        $i = 0;
        foreach ($data as $key => $value) {
            $logical_connector = "AND";

            foreach (self::ALIAS_X_CONNECTOR as $alias => $connector) {
                if (!starts_with($key, "{$alias}_")) continue;
                $key = str_replace($alias, "", $key);
                $logical_connector = $connector;
            }

            if ($i === 0) {
                $logical_connector = "";
            }

            $value_is_column = false;
            $bind = true;

            foreach (self::ALIAS_X_TABLE_ALIAS as $alias => $table_alias) {
                if (
                    !is_string($value) || !starts_with($value, "{$alias}_")
                ) continue;

                $value_is_column = true;
                $bind = false;
                $value = str_replace("{$alias}_", "$table_alias.", $value);
            }

            foreach (self::ALIAS_X_TABLE_ALIAS as $alias => $table_alias) {
                if (!starts_with($key, "{$alias}_")) continue;

                $column = str_replace("{$alias}_", "$table_alias.", $key);
                $logical_operator = "=";

                if (!$value_is_column) {
                    if (str_contains($value, ",")) {
                        $logical_operator = "IN";
                        $value = "($value)";
                        $bind = false;
                    }
                    if (str_contains($value, "*")) {
                        $logical_operator = "LIKE";
                        $value = str_replace("*", "%", $value);
                    }
                    if (starts_with($value, "!")) {
                        $logical_operator = "<>";
                        $value = str_replace("!", "", $value);
                    }

                    $data[$key] = $value;
                }

                $condition = "$logical_connector $column $logical_operator ";
                $condition .= $bind ? ":$key" : $value;
                $wheres[] = $condition;

                $i++;
            }
        }

        $wheres = implode(" ", $wheres);

        return $wheres;
    }

    public function select(array $columns, array $data): array
    {
        $columns = implode(", ", $columns);
        $table_name = $data["table"];
        $table_alias = $this->getTableAlias($table_name);

        $sql = "SELECT $columns FROM $table_name $table_alias";

        if (array_key_exists("join", $data)) {
            $joins = $data["join"];

            foreach ($joins as $join_config) {
                $table_alias = $this->getTableAlias($join_config["table"]);
                $on = $this->convertArrayToWhereCondition($join_config["conditions"]);
                $sql .= " $join_config[type] JOIN $join_config[table] $table_alias ON $on";
            }
        }

        $wheres = $this->convertArrayToWhereCondition($data);
        $sql .= " WHERE $wheres";

        if (array_key_exists("group_by", $data)) {
            $group_by_columns = implode(",", $data["group_by"]);
            $sql .= " GROUP BY $group_by_columns";
        }

        if (array_key_exists("order", $data)) {
            extract($data["order"]);

            $data["order_column"] = $column;
            $data["order_direction"] = $direction;
            unset($data["order"]);

            $sql .= " ORDER BY :order_column :order_direction";
        }

        if (array_key_exists("offset", $data) && array_key_exists("limit", $data)) {
            $sql .= " LIMIT :offset, :limit";
        } else if (array_key_exists("limit", $data)) {
            $sql .= " LIMIT :limit";
        }

        $results = $this->conn->select($sql, $data);
        return $results;
    }

    public function insert(string $table, array $rows): string|false
    {
        $columns = [];
        $values = [];
        $data = [];

        foreach ($rows as $row) {
            $keys = array_keys($row);
            $columns = array_merge($columns, $keys);
            $columns = array_unique($columns);
        }

        foreach ($rows as $i => $row) {
            $row_values = [];

            foreach ($columns as $column) {
                $row_value = $row[$column];
                $bind_key = "{$column}$i";
                $data[$bind_key] = $row_value;
                $row_values[] = ":$bind_key";
            }

            $row_values = implode(", ", $row_values);
            $values[] = $row_values;
        }

        $columns = implode(", ", $columns);
        $values = array_map(function ($row) {
            return "($row)";
        }, $values);
        $values = implode(", ", $values);

        $sql = "INSERT INTO $table ($columns) VALUES $values";
        $success = $this->conn->insert($sql, $data);
        if (!$success) return false;

        $last_id = $this->conn->getLastInsertedId();
        return $last_id;
    }

    public function startTransaction()
    {
        return $this->conn->startTransaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

    public function rollback()
    {
        return $this->conn->rollBack();
    }
}
