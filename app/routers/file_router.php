<?php 

namespace App\Core\Routers;

use App\Core\Router;

function file_router(Router $router) {
    $router->post("/api/files", "\\App\\Controllers\\FilesController::insertFile");
}
