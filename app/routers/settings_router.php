<?php 

namespace App\Core\Routers;

use App\Core\Router;

function settings_router(Router $router) {
    $router->patch("/api/settings", "\\App\\Controllers\\SettingsController::updateSettings");
}
