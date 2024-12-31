<?php

namespace App\Config\Routes;

use App\Core\Router;

$router = new Router();

// Rotas de Home
$router->get("/", "\\App\\Controllers\\HomeController::index");

// Rotas de Posts
$router->get("/posts/:slug_or_id", "\\App\\Controllers\\PostsController::index");
$router->get("/api/posts", "\\App\\Controllers\\PostsController::listPosts");

// Rotas de Testes
$router->get("/api/ping", "\\App\\Controllers\\TestsController::ping");

$router->setHandlers([
    "not_found_page" => "\\App\\Controllers\\NotFoundController::index",
    "error"          => "\\App\\Middlewares\\ErrorMiddleware::execute"
]);

// Roda o roteador
$router->listen();
