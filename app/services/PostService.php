<?php

namespace App\Services;

use App\Core\DatabaseService;
use App\Exceptions\InvalidInputException;
use App\Exceptions\MissingParamException;

class PostService extends DatabaseService
{
    protected function generatePostSlug(string $new_title, string $post_id): string
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

    protected function treatPostData(array &$data): void 
    {
        extract($data);

        if (isset($title)) {
            $title = remove_multiple_whitespaces($title);
            $title = htmlspecialchars($title);
            $data["title"] = $title;
        }

        if (isset($categories)) {
            if (is_string($categories)) {
                $categories = explode(",", $categories);
            }

            if (is_array($categories)) {
                $categories = array_map("trim", $categories);
                $categories = array_filter($categories, function ($category_name) {
                    return (bool)$category_name;
                });
            }

            $data["categories"] = $categories;
        }
    }

    protected function validatePostData(array $data): void 
    {
        extract($data);

        if (isset($title) && !$title) 
            throw new MissingParamException('"título do post"');

        if (isset($categories)) {
            $num_categories = count($categories);
            if ($num_categories === 0) {
                throw new InvalidInputException("Obrigatório informar pelo menos 1 categoria para o post.");
            }
        }
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
            "p.deleted"   => "0",
            "p.published" => "1",
            "order"       => [
                "column"    => "p.created_at",
                "direction" => "DESC",
            ],
            "limit"       => $limit
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
        unset($data["id"]);
        unset($data["slug"]);
        unset($data["deleted"]);
        unset($data["deleted_at"]);
        unset($data["created_at"]);
        unset($data["updated_at"]);
        unset($data["published"]);
        unset($data["published_at"]);

        $this->treatPostData($updates);
        $this->validatePostData($updates);

        extract($updates);

        if (isset($title)) {
            $slug = $this->generatePostSlug($title, $post_id); 
            $updates["slug"] = $slug;
        }

        if (isset($categories)) {
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

    public function createPost(array $data): array|false 
    {
        unset($data["id"]);
        unset($data["slug"]);
        unset($data["deleted"]);
        unset($data["deleted_at"]);
        unset($data["created_at"]);
        unset($data["updated_at"]);
        unset($data["published"]);
        unset($data["published_at"]);

        $data = array_merge([
            "title"      => "",
            "text"       => "",
            "categories" => []
        ], $data);

        $this->treatPostData($data);
        $this->validatePostData($data);
        
        $success = $this->insert("Post", [$data]);
        if (!$success) return false;

        $post_id = $success;
        
        $slug = $this->generatePostSlug($data["title"], $post_id);
        $post = $this->updatePost($post_id, ["slug"=> $slug]);
        
        return $post;
    }

    public function publishPost(string $post_id): array|false 
    {
        $publish_date = date(DEFAULT_DATABASE_DATETIME_FORMAT);
        $post = $this->updatePost($post_id, [
            "published"    => "1",
            "published_at" => $publish_date
        ]);
        return $post;
    }
}
