<?php 

namespace App\Core\Routers;

use App\Core\Router;

function category_router(Router $router) {
    $router->get("/api/categories/", "\\App\\Controllers\\CategoriesController::listCategories");
    $router->post("/api/categories/", "\\App\\Controllers\\CategoriesController::insertCategory");
    $router->delete("/api/categories/:id_or_name", "\\App\\Controllers\\CategoriesController::deleteCategory");
}
