<?php

namespace App\Controllers;

use App\Services\PostService;
use App\Services\CommentsService;

use App\Exceptions\MissingParamException;
use App\Exceptions\InvalidFormatException;

use App\Core\Request;
use App\Core\Response;

class PostsController extends ComponentsController
{
    private array $post;

    // Api

    public function listPosts(Request $request, Response $response)
    {
        $get = $request->getGet();
        extract($get);

        if (!isset($page)) {
            throw new MissingParamException("page");
        }

        if (!isset($size)) {
            throw new MissingParamException("size");
        }

        if (!is_numeric($page)) {
            throw new InvalidFormatException("page", ["numérico"]);
        }

        if (!is_numeric($size)) {
            throw new InvalidFormatException("size", ["numérico"]);
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

    // Helpers

    private function getCurrentPost(): array|null
    {
        if (isset($this->post)) {
            return $this->post;
        }
        
        $params = $this->request->getParams();
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

        if ($is_id) {
            $id = (int)$slug_or_id;
            $post = $post_service->getPostById($id, $columns);
        } else if ($is_slug) {
            $slug = (string)$slug_or_id;
            $post = $post_service->getPostBySlug($slug, $columns);
        }

        $this->post = $post;

        return $post;
    }

    // Views 

    public function commentList()
    {
        $post = $this->getCurrentPost();

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

    public function postArticle() 
    {
        $post = $this->getCurrentPost();
        $this->view("posts/post-article", ["post" => $post]);
    }

    public function index()
    {
        $post = $this->getCurrentPost();

        $keywords = explode(",", $post["category_names"]);
        $session = $this->request->getSession();
        $settings = $session["settings"];
        extract($settings);

        $this->page("posts", [
            "title"       => "$post[title] - $blog_name",
            "description" => "Teste",
            "keywords"    => $keywords,
        ]);
    }

    public function html(Request $request)
    {
        $this->views["post-articles"] = "postArticle";
        $this->views["comment-list"] = "commentList";
        $this->views["comment-form"] = "commentForm";
        $this->views["index"] = "index";
        $this->views["default"] = "index";

        parent::html($request);
    }
}
