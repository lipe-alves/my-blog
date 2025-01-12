<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PostService;
use App\Core\Request;

class HomeController extends Controller
{
    public function postList()
    {
        $get = $this->request->getGet();
        $fetch_recent_posts = count(array_keys($get)) === 0;

        $post_service = new PostService();
        $post_list = [];
        $columns = [
            "p.id",
            "p.slug",
            "p.title",
            "p.text",
            "p.created_at",
            "p.updated_at",
            "category_names"
        ];

        if ($fetch_recent_posts) {
            $post_list = $post_service->getRecentPosts($columns);
        } else {
            extract($get);

            $filter_params = [
                "p.deleted" => "0",
                "offset"    => 0,
                "limit"     => 5,
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

            $post_list = $post_service->getPosts($columns, $filter_params);
        }

        $this->view("home/post-list", [
            "post_list" => $post_list,
            "query"     => $get
        ]);
    }

    public function postFilters()
    {
        $get = $this->request->getGet();
        $this->component("filters", ["query" => $get]);
    }

    public function index()
    {
        $get = $this->request->getGet();

        $post_service = new PostService();

        $recent_posts = $post_service->getRecentPosts([
            "p.id",
            "p.slug",
            "p.title",
            "p.text",
            "p.created_at",
            "p.updated_at",
            "category_names"
        ]);
        $post_count = count($recent_posts);
        $no_posts = $post_count === 0;
        $show_no_posts_msg = json_encode($no_posts);
        $show_filter_title = json_encode(
            (array_key_exists("search", $get) && (bool)$get["search"]) ||
                (array_key_exists("category", $get) && (bool)$get["category"])
        );

        $session = $this->request->getSession();
        $settings = $session["settings"];
        extract($settings);

        $this->view("home", [
            "title"             => $blog_name,
            "description"       => $blog_catchline,
            "keywords"          => [],
            "recent_posts"      => $recent_posts,
            "post_count"        => $post_count,
            "no_posts"          => $no_posts,
            "show_no_posts_msg" => $show_no_posts_msg,
            "show_filter_title" => $show_filter_title
        ]);
    }

    public function home(Request $request)
    {
        $get = $request->getGet();
        $view = "index";

        if (array_key_exists("view", $get)) {
            $view = $get["view"];
        }

        switch ($view) {
            case "post-list":
                return $this->postList();
            case "post-filters":
                return $this->postFilters();
            default:
                return $this->index();
        }
    }
}
