<?php

namespace App\Controllers;

use App\Services\PostService;
use App\Services\CommentsService;

use App\Exceptions\MissingParamException;
use App\Exceptions\InvalidFormatException;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class PostsController extends Controller
{
    public function listPosts(Request $request, Response $response)
    {
        extract($request->getGet());

        if (!isset($page)) {
            throw new MissingParamException("page");
        }

        if (!isset($size)) {
            throw new MissingParamException("size");
        }

        if (!is_numeric($page)) {
            throw new InvalidFormatException("page", ["int"]);
        }

        if (!is_numeric($size)) {
            throw new InvalidFormatException("size", ["int"]);
        }

        if (!isset($columns)) {
            $columns = "p.*";
        }

        $columns = explode(",", $columns);
        $columns = array_map("trim", $columns);

        $page = (int)$page;
        $size = (int)$size;

        $limit = $size + $size * $page;
        $offset = $limit - $size;
        $offset = max($offset, 0);
        $offset = min($offset, $limit);

        $filter_params = [
            "post_deleted" => "0",
            "offset"       => $offset,
            "limit"        => $limit + 1,
            "order"        => [
                "column"    => "p.created_at",
                "direction" => "DESC",
            ],
        ];

        if (isset($category)) {
            if (is_numeric($category)) {
                $filter_params["category_id"] = $category;
            } else {
                $filter_params["category_name"] = $category;
            }
        }

        if (isset($search)) {
            $search_text_expression = "*$search*";
            $filter_params["&&post_title"] = $search_text_expression;
            $filter_params["||post_text"] = $search_text_expression;
        }

        $post_service = new PostService();
        $posts = $post_service->getPosts($columns, $filter_params);

        $total_posts = count($posts);
        $next_page = $total_posts > $size;

        if ($next_page) {
            unset($posts[$total_posts - 1]);
        }

        $result = [
            "list"      => $posts,
            "next_page" => $next_page
        ];

        $response->setStatus(200)->setJson($result)->send();
    }

    public function index(Request $request)
    {
        $params = $request->getParams();
        $slug_or_id = $params["slug_or_id"];
        $is_id = is_numeric($slug_or_id);
        $is_slug = is_string($slug_or_id);
        $post = null;

        $columns = [
            "p.id",
            "p.slug",
            "p.title",
            "p.text",
            "p.created_at",
            "p.updated_at",
            "category_names"
        ];

        $post_service = new PostService();
        $comments_service = new CommentsService();

        if ($is_id) {
            $id = (int)$slug_or_id;
            $post = $post_service->getPostById($id, $columns);
        } else if ($is_slug) {
            $slug = (string)$slug_or_id;
            $post = $post_service->getPostBySlug($slug, $columns);
        }

        if (!$post || !$post["id"]) {
            return $this->view("post-not-found", [
                "title"       => "Teste",
                "description" => "Teste",
                "keywords"    => [],
            ]);
        }

        $comments = $comments_service->getPostComments($post["id"]);

        $keywords = explode(",", $post["category_names"]);

        $this->view("post", [
            "title"        => $post["title"],
            "description"  => "Teste",
            "keywords"     => $keywords,
            "post"         => $post,
            "comments"     => $comments
        ]);
    }
}
