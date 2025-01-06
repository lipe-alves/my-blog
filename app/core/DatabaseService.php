<?php

namespace App\Core;

class DatabaseService
{
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
        $table_x_alias = [
            "Post"            => "p",
            "Comment"         => "comm",
            "Category"        => "c",
            "Post_x_Category" => "pc",
            "Reader"          => "r",
        ];

        if (!array_key_exists($table_name, $table_x_alias)) {
            return strtolower(substr($table_name, 0, 1));
        }

        return $table_x_alias[$table_name];
    }

    private function convertKeyValuePairToWhereCondition(string $key, mixed $value, array &$wheres)
    {
        $alias_x_column = [
            "post"     => "p",
            "category" => "c",
            "comment"  => "comm",
            "reader"   => "r",
        ];

        $logical_operator = "AND";
        $new_key = $key;

        if (starts_with($key, "&&")) {
            $new_key = str_replace("&&", "", $key);
            $logical_operator = "AND";
        }

        if (starts_with($key, "||")) {
            $new_key = str_replace("||", "", $key);
            $logical_operator = "OR";
        }

        foreach ($alias_x_column as $alias => $column) {
            if (!str_contains($new_key, "{$alias}_")) continue;

            $column = str_replace("{$alias}_", "$column.", $new_key);
            $operator = "=";

            if (str_contains($value, ",")) {
                $operator = "IN";
            }
            if (str_contains($value, "*")) {
                $operator = "LIKE";
                $value = str_replace("*", "%", $value);
            }
            if (starts_with($value, "!")) {
                $operator = "<>";
                $value = str_replace("!", "", $value);
            }


            $wheres[] = "$logical_operator $column $operator :$new_key";
        }
    }

    public function select(array $columns, array $data): array
    {
        $columns = implode(", ", $columns);
        $table_name = $data["table"];
        $table_alias = $this->getTableAlias($table_name);

        $sql = "SELECT $columns FROM $table_name $table_alias";

        if (array_key_exists("join", $data)) {
            $joins = $data["join"];

            foreach ($joins as $join) {
                extract($join);

                $table_alias = $this->getTableAlias($table_name);
                $on = [];

                foreach ($conditions as $key => $value) {
                    $this->convertKeyValuePairToWhereCondition($key, $value, $on);
                }

                $on = implode(" ", $conditions);

                $sql .= " $type JOIN $table_name $table_alias ON 1 = 1 AND $on";
            }
        }

        $wheres = ["1 = 1"];

        foreach ($data as $key => $value) {
            $this->convertKeyValuePairToWhereCondition($key, $value, $wheres);
        }

        $wheres = implode(" ", $wheres);
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

        if ($data["table"] === "Comment") {
            file_put_contents("sql.sql", $sql);
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
                $data["{$column}$i"] = $row_value;
                $row_values[] = $row_value;
            }

            $row_values = implode(", ", $row_values);
            $values[] = $row_values;
        }

        $columns = implode(", ", $columns);
        $values = array_map(function ($row) {
            return "($row)";
        }, $values);
        $values = implode(", ", $values);

        $success = $this->conn->insert("INSERT INTO $table ($columns) VALUES $values", $data);
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
