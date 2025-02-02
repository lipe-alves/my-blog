<?php 

namespace App\Core\Routers;

use App\Core\Router;

function home_router(Router $router) {
    $router->get("/", "\\App\\Controllers\\HomeController::html");
}
