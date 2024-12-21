<?php

namespace App\Services;

use App\Core\DatabaseConnection;

class PostService
{
    public static function getPosts(array $columns, array $data)
    {
        $conn = DatabaseConnection::create();

        $fetch_categories = false;

        foreach ($columns as $i => $column) {
            if (str_contains($column, "c.")) {
                $fetch_categories = true;
            } else if (str_contains($column, "category_names")) {
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

        if ($fetch_categories) {
            $sql .= " LEFT JOIN Post_x_Category pc ON pc.post_id = p.id";
            $sql .= " LEFT JOIN Category c ON c.id = pc.category_id";
        }

        $wheres = ["1 = 1"];

        if (array_key_exists("post_id", $data)) {
            $wheres[] = "p.id = :post_id";
        }

        if (array_key_exists("post_deleted", $data)) {
            $wheres[] = "p.deleted = :post_deleted";
        }

        $wheres = implode(" AND ", $wheres);
        $sql .= " WHERE $wheres";

        if (array_key_exists("order", $data)) {
            extract($data["order"]);

            $data["order_column"] = $column;
            $data["order_direction"] = $column;
            unset($data["order"]);

            $sql .= " ORDER BY :order_column :order_direction";
        }

        if (array_key_exists("limit", $data)) {
            $sql .= " LIMIT :limit";
        }

        $posts = $conn->select($sql, $data);
        return $posts;
    }

    public static function getPostById(string $id, array $columns = ["*"])
    {
        $posts = self::getPosts($columns, [
            "post_id" => $id
        ]);

        return count($posts) === 0 ? null : $posts[0];
    }

    public static function getRecentPosts(array $columns = ["*"], int $limit = 5)
    {
        $posts = self::getPosts($columns, [
            "post_deleted" => "0",
            "order"        => [
                "column"    => "p.created_at",
                "direction" => "ASC",
            ],
            "limit"        => $limit
        ]);

        return $posts;
    }

    public static function getPostCategories(string $post_id, array $columns = ["c.*"], int $limit = null)
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
