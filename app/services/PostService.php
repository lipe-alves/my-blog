<?php

namespace App\Services;

use App\Core\DatabaseConnection;
use App\Core\DatabaseService;

class PostService extends DatabaseService
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

        foreach ($data as $key => $value) {
            if (str_contains($key, "category")) {
                $fetch_categories = true;
            }
        }

        if ($fetch_categories) {
            if (!array_key_exists("join", $data)) {
                $data["join"] = [];
            }

            $data["join"][] = [
                "type"       => "LEFT",
                "table"      => "Post_x_Category",
                "conditions" => [
                    "pc.post_id" => "p.id"
                ]
            ];
            $data["join"][] = [
                "type"       => "LEFT",
                "table"      => "Category",
                "conditions" => [
                    "c.id" => "pc.category_id"
                ]
            ];
        }

        $data["table"] = "Post";

        $posts = self::get($columns, $data);

        return $posts;
    }

    public static function getPostById(string $id, array $columns = ["*"])
    {
        $posts = self::getPosts($columns, [
            "post_id" => $id
        ]);

        return count($posts) === 0 ? null : $posts[0];
    }

    public static function getPostBySlug(string $slug, array $columns = ["*"])
    {
        $posts = self::getPosts($columns, [
            "post_slug" => $slug
        ]);

        return count($posts) === 0 ? null : $posts[0];
    }

    public static function getRecentPosts(array $columns = ["*"], int $limit = 5)
    {
        $posts = self::getPosts($columns, [
            "post_deleted" => "0",
            "order"        => [
                "column"    => "p.created_at",
                "direction" => "DESC",
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
