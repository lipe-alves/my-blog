<?php

namespace App\Config\Routes;

use App\Core\Router;

$router = new Router();

// Rotas de Home
$router->get("/", "\\App\\Controllers\\HomeController::index");
$router->get("/post-list", "\\App\\Controllers\\HomeController::postList");

// Rotas de Posts
$router->get("/posts/:slug_or_id", "\\App\\Controllers\\PostsController::index");
$router->get("/api/posts", "\\App\\Controllers\\PostsController::listPosts");

// Rotas de Comentários
$router->get("/api/comments", "\\App\\Controllers\\CommentsController::listComments");
$router->post("/api/comments", "\\App\\Controllers\\CommentsController::insertComment");

// Rotas de Testes
$router->get("/api/ping", "\\App\\Controllers\\TestsController::ping");

$router->setHandlers([
    "not_found_page" => "\\App\\Controllers\\NotFoundController::index",
    "global"         => "\\App\\Middlewares\\SessionMiddleware::execute",
    "error"          => "\\App\\Middlewares\\ErrorMiddleware::execute"
]);

// Roda o roteador
$router->listen();
