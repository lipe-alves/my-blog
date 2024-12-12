<?php

namespace App\Models;

use App\Core\DatabaseConnection;

class PostModel
{
    public static function getPostBySlug(string $slug, array $columns = ["*"]): array|null
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
            $sql .= " LEFT JOIN Category c ON c.name = p.category_name";
        }

        $sql .= " WHERE slug = ? LIMIT 1";

        $data = [$slug];

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
            $sql .= " LEFT JOIN Category c ON c.name = p.category_name";
        }

        $sql .= " WHERE p.deleted = 0 ORDER BY p.created_at DESC";
        $data = [];

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $data["limit"] = $limit;
        }

        $posts = $conn->select($sql, $data);

        return $posts;
    }

    public static function getPostCategories(string $post_slug, array $columns = ["c.*"], int|null $limit = null): array
    {
        $conn = DatabaseConnection::create();
        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Post_x_Category pxc
            LEFT JOIN Category c ON pxc.category_id = c.id
            WHERE pxc.post_slug = :post_slug
        ";
        $data = [
            "post_slug" => $post_slug
        ];

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $data["limit"] = $limit;
        }

        $posts = $conn->select($sql, $data);

        return $posts;
    }
}
