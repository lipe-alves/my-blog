<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PostService;
use App\Core\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $get = $request->getGet();
        $recent_posts = PostService::getRecentPosts([
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

        $session = $request->getSession();
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
}
