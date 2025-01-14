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
    // Api

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
            "p.deleted" => "0",
            "offset"    => $offset,
            "limit"     => $limit + 1,
            "order"     => [
                "column"    => "p.created_at",
                "direction" => "DESC",
            ],
        ];

        if (isset($category)) {
            if (is_numeric($category)) {
                $filter_params["c.id"] = $category;
            } else {
                $filter_params["c.name"] = $category;
            }
        }

        if (isset($search)) {
            $search_text_expression = "*$search*";
            $filter_params["&&p.title"] = $search_text_expression;
            $filter_params["||p.text"] = $search_text_expression;
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

    // Views 

    private function getPostFromSlugOrId(string $slug_or_id): array|null
    {
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

        if ($is_id) {
            $id = (int)$slug_or_id;
            $post = $post_service->getPostById($id, $columns);
        } else if ($is_slug) {
            $slug = (string)$slug_or_id;
            $post = $post_service->getPostBySlug($slug, $columns);
        }

        return $post;
    }

    public function commentList()
    {
        $params = $this->request->getParams();
        $slug_or_id = $params["slug_or_id"];
        $post = $this->getPostFromSlugOrId($slug_or_id);

        $comments_service = new CommentsService();

        $post_comments = $comments_service->getPostComments($post["id"], [
            "comm.*",
            "r.first_name",
            "r.last_name",
            "r.photo"
        ]);

        $this->view("posts/comment-list", ["post_comments" => $post_comments]);
    }

    public function commentForm()
    {
        $get = $this->request->getGet();
        extract($get);

        $data = [
            "post" => [
                "id" => $post_id
            ]
        ];

        if (isset($reply_to)) {
            $data["reply_to_comment"] = [
                "id" => $reply_to
            ];
        }

        $this->view("posts/add-comment", $data);
    }

    public function index()
    {
        $params = $this->request->getParams();
        $slug_or_id = $params["slug_or_id"];
        $post = $this->getPostFromSlugOrId($slug_or_id);

        $keywords = explode(",", $post["category_names"]);

        $session = $this->request->getSession();
        $settings = $session["settings"];
        extract($settings);

        $this->view("posts", [
            "title"       => "$post[title] - $blog_name",
            "description" => "Teste",
            "keywords"    => $keywords,
            "post"        => $post,
        ]);
    }

    public function page(Request $request)
    {
        $get = $request->getGet();
        $view = "index";

        if (array_key_exists("view", $get)) {
            $view = $get["view"];
        }

        switch ($view) {
            case "comment-list":
                return $this->commentList();
            case "comment-form":
                return $this->commentForm();
            case "index":
            default:
                return $this->index();
        }
    }
}
