<?php

namespace App\Services;

use App\Core\DatabaseConnection;

class CategoryService
{
    public static function getCategories(array $columns, array $data)
    {
        $conn = DatabaseConnection::create();

        foreach ($columns as $i => $column) {
            if ($column === "post_count") {
                $columns[$i] = "(SELECT COUNT(DISTINCT pc.post_id) FROM Post_x_Category pc WHERE pc.category_id = c.id) AS post_count";
            }
        }

        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Category c";

        $wheres = ["1 = 1"];

        if (array_key_exists("category_id", $data)) {
            $wheres[] = "c.id = :category_id";
        }

        if (array_key_exists("category_name", $data)) {
            $wheres[] = "c.name = :category_name";
        }

        if (array_key_exists("category_deleted", $data)) {
            $wheres[] = "c.deleted = :category_deleted";
        }
        
        $wheres = implode(" AND ", $wheres);
        $sql .= " WHERE $wheres";

        if (array_key_exists("limit", $data)) {
            $sql .= " LIMIT :limit";
        }
        
        $categories = $conn->select($sql, $data);
        return $categories;
    }

    public static function getCategoryById(string $id, array $columns = ["*"])
    {
        $categories = self::getCategories($columns, ["category_id" => $id]);
        return count($categories) === 0 ? null : $categories[0];
    }

    public static function getCategoryByName(string $name, array $columns = ["*"])
    {
        $categories = self::getCategories($columns, ["category_name" => $name]);
        return count($categories) === 0 ? null : $categories[0];
    }

    public static function getAllCategories(array $columns = ["*"], $limit = null)
    {
        $categories = self::getCategories($columns, ["limit" => $limit]);
        return $categories;
    }
}
