<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

use App\Services\CategoryService;
use App\Services\SettingsService;

class SessionMiddleware extends Middleware
{
    public function execute(Request $request, Response $response, \Exception $exception = null)
    {
        $session = $request->getSession();
        
        $categories_loaded = array_key_exists("categories", $session) && count($session["categories"]) > 0;
        $settings_loaded = array_key_exists("settings", $session);

        if (!$categories_loaded) {
            $categories = CategoryService::getAllCategories([
                "c.*",
                "post_count"
            ]);

            $request->setSession("categories", $categories);
        }

        if (!$settings_loaded) {
            $request->setSession("settings", SettingsService::getAll());
        }
    }
}
