<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CategoryModel;
use App\Models\PostModel;

class HomeController extends Controller
{
    public function index()
    {
        $categories = CategoryModel::getAllCategories();
        $recent_posts = PostModel::getRecentPosts([
            "p.slug",
            "p.title",
            "p.text",
            "p.created_at",
            "c.name AS category_name"
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
