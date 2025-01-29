<?php

namespace App\Config\Routes;

use App\Core\Router;

$router = new Router();

$router->addMiddleware("*", "\\App\\Middlewares\\SessionMiddleware::execute");
$router->addMiddleware("*", "\\App\\Middlewares\\AdminMiddleware::execute");

// Rotas de Home
$router->get("/", "\\App\\Controllers\\HomeController::page");

// Rotas de Posts
$router->get("/posts/:slug_or_id", "\\App\\Controllers\\PostsController::page");
$router->get("/api/posts", "\\App\\Controllers\\PostsController::listPosts");

// Rotas de Comentários
$router->get("/api/comments", "\\App\\Controllers\\CommentsController::listComments");
$router->post("/api/comments", "\\App\\Controllers\\CommentsController::insertComment");

// Rotas de categorias
$router->delete("/api/categories/:name_or_id", "\\App\\Controllers\\CategoriesController::deleteCategory");

// Rotas de Configurações
$router->patch("/api/settings", "\\App\\Controllers\\SettingsController::updateSettings");

// Rotas de Admin
$router->get("/admin", "\\App\\Controllers\\AdminController::page");
$router->post("/api/admin/authenticate", "\\App\\Controllers\\AdminController::authenticate");

// Rotas de Testes
$router->get("/api/ping", "\\App\\Controllers\\TestsController::ping");

$router->setHandlers([
    "not_found_page" => "\\App\\Controllers\\NotFoundController::index",
    "error"          => "\\App\\Middlewares\\ErrorMiddleware::execute"
]);

// Roda o roteador
$router->listen();
