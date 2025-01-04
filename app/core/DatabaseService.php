<?php

namespace App\Core;

class DatabaseService
{
    private static function getTableAlias(string $table_name)
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

    private static function convertKeyValuePairToWhereCondition(string $key, mixed $value, array &$wheres)
    {
        $alias_x_column = [
            "post"     => "p",
            "category" => "c",
            "comment"  => "c",
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

    public static function get(array $columns, array $data): array
    {
        $conn = DatabaseConnection::create();

        $columns = implode(", ", $columns);
        $table_name = $data["table"];
        $table_alias = self::getTableAlias($table_name);

        $sql = "SELECT $columns FROM $table_name $table_alias";

        if (array_key_exists("join", $data)) {
            $joins = $data["join"];

            foreach ($joins as $join) {
                extract($join);

                $table_alias = self::getTableAlias($table_name);
                $conditions = [];

                foreach ($on as $key => $value) {
                    self::convertKeyValuePairToWhereCondition($key, $value, $conditions);
                }

                $conditions = implode(" ", $conditions);

                $sql .= " $type JOIN $table_name $table_alias ON 1 = 1 AND $conditions";
            }
        }

        $wheres = ["1 = 1"];

        foreach ($data as $key => $value) {
            self::convertKeyValuePairToWhereCondition($key, $value, $wheres);
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

        $results = $conn->select($sql, $data);
        return $results;
    }
}
