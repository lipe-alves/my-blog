<?php

namespace App\Services;

use App\Core\DatabaseService;
use App\Exceptions\MissingParamException;

class PostService extends DatabaseService
{
    private function generatePostSlug(string $new_title, string $post_id): string
    {
        $slug = $new_title;
        $slug = preg_replace("/[^\w]/u", " ", $slug);
        $slug = remove_accents($slug);
        $slug = remove_multiple_whitespaces($slug);
        $slug = mb_strtolower($slug, "UTF-8");
        $slug = str_replace(" ", "-", $slug);

        $post = $this->getPostBySlug($slug, ["p.id"]);
        $slug_already_exists = isset($post) && $post["id"] !== $post_id;
        if ($slug_already_exists) {
            $slug .= "-$post_id";
        }

        return $slug;
    }

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

    public function getPostCategories(string $post_id, array $columns = ["c.*"])
    {
        $category_service = new CategoryService();
        return $category_service->getPostCategories($post_id, $columns);
    }

    public function updatePost(string $post_id, array $updates): array|false 
    {
        unset($updates["id"]);
        unset($updates["slug"]);
        unset($updates["created_at"]);
        unset($updates["updated_at"]);

        extract($updates);

        if (isset($title)) {
            $title = remove_multiple_whitespaces($title);
            $title = htmlspecialchars($title);
            if (!$title) throw new MissingParamException('"título do post"');
            
            $updates["title"] = $title;

            $slug = $this->generatePostSlug($title, $post_id); 
            $updates["slug"] = $slug;
        }

        if (isset($categories)) {
            if (is_string($categories)) {
                $categories = explode(",", $categories);
                $categories = array_map("trim", $categories);
            }

            $category_service = new CategoryService($this->conn);

            $success = $category_service->removeCategoriesFromPost($post_id);
            if (!$success) return false;
            
            foreach ($categories as $id_or_name) {
                $success = $category_service->addCategoryToPost($post_id, $id_or_name);
                if (!$success) return false;
            }

            unset($updates["categories"]);
        }
        
        $success = $this->update("Post", $updates, ["p.id" => $post_id]);
        if (!$success) return false;

        $post = $this->getPostById($post_id);
        
        return $post;
    }
}
