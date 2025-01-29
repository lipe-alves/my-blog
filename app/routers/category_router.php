<?php 

namespace App\Core\Routers;

use App\Core\Router;

function category_router(Router $router) {
    $router->delete("/api/categories/:name_or_id", "\\App\\Controllers\\CategoriesController::deleteCategory");
}
