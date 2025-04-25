<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\CommentsModel;

use App\Exceptions\MissingParamException;
use App\Exceptions\InvalidFormatException;
use App\Exceptions\InternalServerException;
use App\Exceptions\ResourceNotFoundException;

use App\Core\Request;
use App\Core\Response;

class PostsController extends ComponentsController
{
    private array $post;

    // Api

    public function listPosts(Request $request, Response $response): void
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

        $page = (int) $page;
        $size = (int) $size;

        $limit = $size + $size * $page;
        $offset = $limit - $size;
        $offset = max($offset, 0);
        $offset = min($offset, $limit);

        $filter_params = [
            "p.deleted" => "0",
            "offset" => $offset,
            "limit" => $limit + 1,
            "order" => [
                "column" => "p.created_at",
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

        $post_service = new PostModel();
        $posts = $post_service->getPosts($columns, $filter_params);

        $total_posts = count($posts);
        $next_page = $total_posts > $size;

        if ($next_page) {
            unset($posts[$total_posts - 1]);
        }

        $result = [
            "list" => $posts,
            "next_page" => $next_page
        ];

        $response->setStatus(200)->setJson($result)->send();
    }

    public function updatePost(Request $request, Response $response): void
    {
        $post = $this->getCurrentPost();
        if (!$post)
            throw new ResourceNotFoundException("Post não encontrado!");

        $updates = $request->getPatch();

        $post_service = new PostModel();

        try {
            $post_service->startTransaction();

            $last_updated_post = $post_service->updatePost($post["id"], $updates);
            $success = $last_updated_post !== false;
            if (!$success)
                throw new InternalServerException();

            $post_service->commit();

            $response->setStatus(200)->setJson($last_updated_post)->send();
        } catch (\Exception $e) {
            $post_service->rollback();
            throw $e;
        }
    }

    public function publishPost(Request $request, Response $response): void
    {
        $post = $this->getCurrentPost();
        if (!$post)
            throw new ResourceNotFoundException("Post não encontrado!");

        $post_service = new PostModel();

        try {
            $post_service->startTransaction();

            $published_post = $post_service->publishPost($post["id"]);
            $success = $published_post !== false;
            if (!$success)
                throw new InternalServerException();

            $post_service->commit();

            $response->setStatus(200)->setJson($published_post)->send();
        } catch (\Exception $e) {
            $post_service->rollback();
            throw $e;
        }
    }

    public function insertPost(Request $request, Response $response): void
    {
        $post_data = $request->getPost();
        $post_service = new PostModel();

        try {
            $post_service->startTransaction();

            $last_created_post = $post_service->createPost($post_data);
            $success = $last_created_post !== false;
            if (!$success)
                throw new InternalServerException();

            $post_service->commit();

            $response->setStatus(200)->setJson($last_created_post)->send();
        } catch (\Exception $e) {
            $post_service->rollback();
            throw $e;
        }
    }

    // Helpers

    private function getBlankPost(): array
    {
        $post = PostModel::BlankModel();
        $post["id"] = "new";
        $post["title"] = "Sem título";
        $post["text"] = "<p>Escreva aqui suas ideias...</p>";
        $post["category_names"] = "";
        $post["created_at"] = date(DEFAULT_DATABASE_DATETIME_FORMAT);
        $post["updated_at"] = date(DEFAULT_DATABASE_DATETIME_FORMAT);
        return $post;
    }

    private function getCurrentPost(): array|null
    {
        if (isset($this->post)) {
            return $this->post;
        }

        $params = $this->request->getParams();
        $slug_or_id = $params["slug_or_id"];

        if ($slug_or_id === "new") {
            $this->post = $this->getBlankPost();
            return $this->post;
        }

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

        $post_service = new PostModel();

        if ($is_id) {
            $id = (int) $slug_or_id;
            $post = $post_service->getPostById($id, $columns);
        } else if ($is_slug) {
            $slug = (string) $slug_or_id;
            $post = $post_service->getPostBySlug($slug, $columns);
        }

        if (!$post) {
            throw new ResourceNotFoundException("Post não encontrado");
        }

        $this->post = $post;

        return $post;
    }

    // Views 

    public function commentList(): void
    {
        $post = $this->getCurrentPost();

        $post_service = new CommentsModel();

        $post_comments = $post_service->getPostComments($post["id"], [
            "comm.*",
            "r.first_name",
            "r.last_name",
            "r.photo"
        ]);

        $this->view("posts/comment-list", ["post_comments" => $post_comments]);
    }

    public function commentForm(): void
    {
        $post = $this->getCurrentPost();
        $get = $this->request->getGet();
        extract($get);

        $data = [
            "post" => [
                "id" => $post["id"]
            ]
        ];

        if (isset($reply_to)) {
            $data["reply_to_comment"] = [
                "id" => $reply_to
            ];
        }

        $this->view("posts/add-comment", $data);
    }

    public function postArticle(): void
    {
        $post = $this->getCurrentPost();
        $this->view("posts/post-article", ["post" => $post]);
    }

    public function index(): void
    {
        $post = $this->getCurrentPost();

        $keywords = explode(",", $post["category_names"]);
        $session = $this->request->getSession();
        $settings = $session["settings"];
        extract($settings);

        $this->page("posts", [
            "layout" => "base-page",
            "title" => "$post[title] - $blog_name",
            "description" => "Teste",
            "keywords" => $keywords,
            "post" => $post
        ]);
    }

    public function html(Request $request): void
    {
        try {
            $this->views["post-article"] = "postArticle";
            $this->views["comment-list"] = "commentList";
            $this->views["comment-form"] = "commentForm";
            $this->views["index"] = "index";
            $this->views["default"] = "index";

            parent::html($request);
        } catch (\Exception $e) {
            if ($e instanceof ResourceNotFoundException) {
                $this->response->redirect(BASE_URI);
            } else {
                throw $e;
            }
        }
    }
}
