<?php

namespace App\Core\Routers;

use App\Core\Router;

function post_router(Router $router): void
{
    $router->get("/posts/:slug_or_id", "\\App\\Controllers\\PostsController::html");
    $router->get("/api/posts", "\\App\\Controllers\\PostsController::listPosts");
    $router->post("/api/posts", "\\App\\Controllers\\PostsController::insertPost");
    $router->patch("/api/posts/:slug_or_id", "\\App\\Controllers\\PostsController::updatePost");
    $router->post("/api/posts/:slug_or_id/publish", "\\App\\Controllers\\PostsController::publishPost");
}
