<?php 

namespace App\Core\Routers;

use App\Core\Router;

function comment_router(Router $router) {
    $router->get("/api/comments", "\\App\\Controllers\\CommentsController::listComments");
    $router->post("/api/comments", "\\App\\Controllers\\CommentsController::insertComment");
}
