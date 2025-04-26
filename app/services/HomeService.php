<?php

namespace App\Services;

use App\Models\PostModel;

class HomeService
{
    public static function getRecentPosts(array $filters = []): array
    {
        extract($filters);

        $columns = [
            "p.id",
            "p.slug",
            "p.title",
            "p.text",
            "p.created_at",
            "p.updated_at",
            "p.published",
            "p.published_at",
            "category_names"
        ];

        $post_service = new PostModel();

        if (isset($category)) {
            if (is_numeric($category)) {
                $filters["c.id"] = $category;
            } else {
                $filters["c.name"] = $category;
            }
        }

        if (isset($search)) {
            $search_text_expression = "*$search*";
            $filters["&&p.title"] = $search_text_expression;
            $filters["||p.text"] = $search_text_expression;
        }

        if (!isset($size)) {
            $size = 5;
        }

        if (!isset($page)) {
            $page = 0;
        }

        $limit = $size + 1;
        $offset = $page * $size;

        $filters["limit"] = $limit;
        $filters["offset"] = $offset;

        $posts = $post_service->getRecentPosts($columns, $filters);

        $n_posts = count($posts);
        $next_page = $n_posts > $size;

        if ($next_page)
            unset($posts[$n_posts - 1]);

        return [
            "results" => $posts,
            "next_page" => $next_page,
            "page" => $page,
            "size" => $size
        ];
    }
}