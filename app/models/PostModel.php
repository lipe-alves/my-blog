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
            if (strpos($column, "c.") !== false) {
                $fetch_categories = true;
                break;
            }
        }

        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Post p";

        if ($fetch_categories) {
            $sql .= " LEFT JOIN Category c ON c.id = p.category_id";
        }

        $sql .= " WHERE id = ? LIMIT 1";

        $data = [$id];

        $posts = $conn->select($sql, $data);
        return count($posts) === 0 ? null : $posts[0];
    }

    public static function getRecentPosts(array $columns = ["*"], int|null $limit = 5): array
    {
        $conn = DatabaseConnection::create();

        $fetch_categories = false;

        foreach ($columns as $column) {
            if (strpos($column, "c.") !== false) {
                $fetch_categories = true;
                break;
            }
        }

        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Post p";

        if ($fetch_categories) {
            $sql .= " LEFT JOIN Category c ON c.id = p.category_id";
        }

        $sql .= " WHERE p.deleted = 0";
        $data = [];

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $data["limit"] = $limit;
        }

        $posts = $conn->select($sql, $data);

        return $posts;
    }

    public static function getPostCategories(string $potst_id, array $columns = ["c.*"], int|null $limit = null): array
    {
        $conn = DatabaseConnection::create();
        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Post_x_Category pxc
            LEFT JOIN Category c ON pxc.category_id = c.id
            WHERE pxc.post_id = :post_id
        ";
        $data = [
            "post_id" => $potst_id
        ];

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $data["limit"] = $limit;
        }

        $posts = $conn->select($sql, $data);

        return $posts;
    }
}
