<?php

namespace App\Controllers;

use App\Services\HomeService;
use App\Core\Request;

class HomeController extends ComponentsController
{
    public function postList(): void
    {
        $session = $this->request->getSession();
        extract($session);

        $filters = $this->request->getGet();

        if (!$is_admin)
            $filters["published"] = "1";

        $post_list = HomeService::getRecentPosts($filters);

        $this->view("home/post-list", [
            "post_list" => $post_list,
            "query" => $filters
        ]);
    }

    public function index(): void
    {
        $session = $this->request->getSession();
        $settings = $session["settings"];
        extract($settings);

        $this->page("home", [
            "layout" => "base-page",
            "title" => $blog_name,
            "description" => $blog_catchline,
            "keywords" => [],
        ]);
    }

    public function html(Request $request): void
    {
        $this->views["post-list"] = "postList";
        $this->views["index"] = "index";
        $this->views["default"] = "index";
        parent::html($request);
    }
}
