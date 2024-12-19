<?php

namespace App\Models;

use App\Core\DatabaseConnection;

class CategoryModel
{
    public static function getCategoryById(string $id, array $columns = ["*"])
    {
        $conn = DatabaseConnection::create();
        $columns = implode(", ", $columns);
        $categories = $conn->select("SELECT $columns FROM Category WHERE id = :id LIMIT 1", ["id" => $id]);
        return count($categories) === 0 ? null : $categories[0];
    }

    public static function getCategoryByName(string $name, array $columns = ["*"])
    {
        $conn = DatabaseConnection::create();
        $columns = implode(", ", $columns);
        $categories = $conn->select("SELECT $columns FROM Category WHERE name = :name LIMIT 1", ["name" => $name]);
        return count($categories) === 0 ? null : $categories[0];
    }

    public static function getAllCategories(array $columns = ["*"], $limit = null)
    {
        $conn = DatabaseConnection::create();

        foreach ($columns as $i => $column) {
            if ($column === "count_posts") {
                $columns[$i] = "(SELECT COUNT(DISTINCT pc.post_id) FROM Post_x_Category pc WHERE pc.category_id = c.id) AS count_posts";
            }
        }

        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Category c";
        $data = null;

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $data = ["limit" => $limit];
        }

        $categories = $conn->select($sql, $data);

        return $categories;
    }
}
