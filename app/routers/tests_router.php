<?php 

namespace App\Core\Routers;

use App\Core\Router;

function tests_router(Router $router) {
    $router->get("/api/ping", "\\App\\Controllers\\TestsController::ping");
    $router->post("/api/remove-accents", "\\App\\Controllers\\TestsController::removeAccents");
}
