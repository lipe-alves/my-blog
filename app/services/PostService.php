<?php

namespace App\Services;

use App\Core\DatabaseConnection;
use App\Core\DatabaseService;

class PostService extends DatabaseService
{
    public function getPosts(array $columns, array $data)
    {
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
            if (str_contains($key, "c.")) {
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

        $posts = $this->select($columns, $data);

        return $posts;
    }

    public function getPostById(string $id, array $columns = ["*"])
    {
        $posts = $this->getPosts($columns, [
            "p.id" => $id
        ]);

        return count($posts) === 0 ? null : $posts[0];
    }

    public function getPostBySlug(string $slug, array $columns = ["*"])
    {
        $posts = $this->getPosts($columns, [
            "p.slug" => $slug
        ]);

        return count($posts) === 0 ? null : $posts[0];
    }

    public function getRecentPosts(array $columns = ["*"], int $limit = 5)
    {
        $posts = $this->getPosts($columns, [
            "p.deleted" => "0",
            "order"     => [
                "column"    => "p.created_at",
                "direction" => "DESC",
            ],
            "limit"     => $limit
        ]);

        return $posts;
    }

    public function getPostCategories(string $post_id, array $columns = ["c.*"], int $limit = null)
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
