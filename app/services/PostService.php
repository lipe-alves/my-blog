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

        foreach ($data as $key => $value) {
            if (str_contains($key, "category")) {
                $fetch_categories = true;
            }
        }

        $columns = implode(", ", $columns);

        $sql = "SELECT $columns FROM Post p";

        if ($fetch_categories) {
            $sql .= " LEFT JOIN Post_x_Category pc ON pc.post_id = p.id";
            $sql .= " LEFT JOIN Category c ON c.id = pc.category_id";
        }

        $wheres = ["1 = 1"];

        $alias_x_column = [
            "post" => "p",
            "category" => "c",
        ];

        foreach ($data as $key => $value) {
            $logical_operator = "AND";
            $new_key = $key;
            
            if (starts_with($key, "&&")) {
                $new_key = str_replace("&&", "", $key);
                $logical_operator = "AND";
            }

            if (starts_with($key, "||")) {
                $new_key = str_replace("||", "", $key);
                $logical_operator = "OR";
            }

            foreach ($alias_x_column as $alias => $column) {
                if (!str_contains($new_key, "{$alias}_")) continue;

                $column = str_replace("{$alias}_", "$column.", $new_key);
                $operator = "=";

                if (str_contains($value, ",")) {
                    $operator = "IN";
                }
                if (str_contains($value, "*")) {
                    $operator = "LIKE";
                    $value = str_replace("*", "%", $value);
                }
                if (starts_with($value, "!")) {
                    $operator = "<>";
                    $value = str_replace("!", "", $value);
                }

                unset($data[$key]);
                $data[$new_key] = $value;

                $wheres[] = "$logical_operator $column $operator :$new_key";
            }
        }

        $wheres = implode(" ", $wheres);
        $sql .= " WHERE $wheres GROUP BY p.id";

        if (array_key_exists("order", $data)) {
            extract($data["order"]);

            $data["order_column"] = $column;
            $data["order_direction"] = $direction;
            unset($data["order"]);

            $sql .= " ORDER BY :order_column :order_direction";
        }

        if (array_key_exists("offset", $data) && array_key_exists("limit", $data)) {
            $sql .= " LIMIT :offset, :limit";
        } else if (array_key_exists("limit", $data)) {
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
