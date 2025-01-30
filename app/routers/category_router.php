<?php 

namespace App\Core\Routers;

use App\Core\Router;

function category_router(Router $router) {
    $router->delete("/api/categories/:id_or_name", "\\App\\Controllers\\CategoriesController::deleteCategory");
}
