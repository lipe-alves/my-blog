<?php 

namespace App\Core\Routers;

use App\Core\Router;

function about_me_router(Router $router) {
    $router->get("/sobre-mim", "\\App\\Controllers\\AboutMeController::html");
}
