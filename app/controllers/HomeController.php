<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CategoryService;
use App\Services\PostService;

class HomeController extends Controller
{
    public function index()
    {
        $categories = CategoryService::getAllCategories([
            "c.*",
            "post_count"
        ]);
        $recent_posts = PostService::getRecentPosts([
            "p.slug",
            "p.title",
            "p.text",
            "p.created_at",
            "category_names"
        ]);

        $this->view("home", [
            "title"        => APP_NAME,
            "description"  => "Teste",
            "keywords"     => [],
            "categories"   => $categories,
            "recent_posts" => $recent_posts,
            "header_image" => BASE_URI . "/public/images/header-plant.png"
        ]);
    }
}
