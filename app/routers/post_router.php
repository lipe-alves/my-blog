<?php 

namespace App\Core\Routers;

use App\Core\Router;

function post_router(Router $router) {
    $router->get("/posts/:slug_or_id", "\\App\\Controllers\\PostsController::html");
    $router->get("/api/posts", "\\App\\Controllers\\PostsController::listPosts");
}
