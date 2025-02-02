<?php 

namespace App\Core\Routers;

use App\Core\Router;

function admin_router(Router $router) {
    $router->get("/admin", "\\App\\Controllers\\AdminController::html");
    $router->post("/api/admin/authenticate", "\\App\\Controllers\\AdminController::authenticate");
}
