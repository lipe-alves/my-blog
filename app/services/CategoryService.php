<?php

namespace App\Services;

use App\Core\DatabaseConnection;
use App\Core\DatabaseService;

class CategoryService extends DatabaseService
{
    public static function getCategories(array $columns, array $data)
    {
        foreach ($columns as $i => $column) {
            if ($column === "post_count") {
                $columns[$i] = "(SELECT COUNT(DISTINCT pc.post_id) FROM Post_x_Category pc WHERE pc.category_id = c.id) AS post_count";
            }
        }

        $data["table"] = "Category";

        $categories = self::get($columns, $data);
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
        $data = [];
        if ($limit !== null) {
            $data["limit"] = $limit;
        }

        $categories = self::getCategories($columns, $data);
        return $categories;
    }
}
