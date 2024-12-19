<?php

namespace App\Models;

use App\Core\DatabaseConnection;

class PostModel
{
    public static function getPostById(string $id, array $columns = ["*"]): array|null
    {
        $conn = DatabaseConnection::create();

        $fetch_categories = false;

        foreach ($columns as $column) {
            if (str_contains($column, "c.")) {
                $fetch_categories = true;
                break;
            }
        }

        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Post p";

        if ($fetch_categories) {
            $sql .= " LEFT JOIN Post_x_Category pc ON pc.post_id = p.id";
            $sql .= " LEFT JOIN Category c ON c.id = pc.category_id";
        }

        $sql .= " WHERE p.id = :id LIMIT 1";

        $data = [
            "id" => $id
        ];

        $posts = $conn->select($sql, $data);
        return count($posts) === 0 ? null : $posts[0];
    }

    public static function getRecentPosts(array $columns = ["*"], int|null $limit = 5): array
    {
        $conn = DatabaseConnection::create();

        foreach ($columns as $i => $column) {
            if (str_contains($column, "category_names")) {
                $columns[$i] = "(
                    SELECT 
                        GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') 
                    FROM 
                        Post_x_Category pc
                        JOIN Category c ON c.id = pc.category_id
                    WHERE
                        pc.post_id = p.id
                ) AS category_names";
            }
        }

        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Post p";

        $sql .= " WHERE p.deleted = 0";
        $data = [];

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $data["limit"] = $limit;
        }

        $posts = $conn->select($sql, $data);

        return $posts;
    }

    public static function getPostCategories(string $post_id, array $columns = ["c.*"], int|null $limit = null): array
    {
        $conn = DatabaseConnection::create();
        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Post_x_Category pxc
            LEFT JOIN Category c ON pxc.category_id = c.id
            WHERE pxc.post_id = :post_id
        ";
        $data = [
            "post_id" => $post_id
        ];

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $data["limit"] = $limit;
        }

        $posts = $conn->select($sql, $data);

        return $posts;
    }
}
