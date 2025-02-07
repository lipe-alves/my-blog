<?php 

namespace App\Core\Routers;

use App\Core\Router;

function admin_router(Router $router) {
    $router->get("/admin", "\\App\\Controllers\\AdminController::html");
    $router->post("/api/admin/login", "\\App\\Controllers\\AdminController::login");
    $router->post("/api/admin/logout", "\\App\\Controllers\\AdminController::logout");
}
