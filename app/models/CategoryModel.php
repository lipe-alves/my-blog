<?php

namespace App\Models;

use App\Core\DatabaseConnection;

class CategoryModel
{
    public static function getCategoryById(string $id, array $columns = ["*"])
    {
        $conn = DatabaseConnection::create();
        $columns = implode(", ", $columns);
        $categories = $conn->select("SELECT $columns FROM Category WHERE id = ? LIMIT 1", ["$id"]);
        return count($categories) === 0 ? null : $categories[0];
    }

    public static function getAllCategories(array $columns = ["*"], $limit = null)
    {
        $conn = DatabaseConnection::create();
        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Category";
        $data = null;

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $data = [$limit];
        }

        $categories = $conn->select($sql, $data);

        return $categories;
    }
}
