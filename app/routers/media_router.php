<?php 

namespace App\Core\Routers;

use App\Core\Router;

function media_router(Router $router) {
    $router->post("/api/media", "\\App\\Controllers\\MediaController::insertFile");
    $router->patch("/api/media", "\\App\\Controllers\\MediaController::updateFile");
}
