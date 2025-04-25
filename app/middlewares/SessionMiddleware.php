<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

use App\Models\CategoryModel;
use App\Models\SettingsModel;

class SessionMiddleware extends Middleware
{
    public function execute(Request $request, Response $response, \Exception $exception = null)
    {
        $session = $request->getSession();

        if (isset($session["last_activity"]) && (time() - $session["last_activity"] > SESSION_TIMEOUT)) {
            $request->clearSession();
            $request->destroySession();
            $request->startSession();
        }

        $request->setSession("last_activity", time());

        $session = $request->getSession();

        $reload_session = array_key_exists("reload", $session) && $session["reload"];
        $categories_loaded = array_key_exists("categories", $session) && count($session["categories"]) > 0;
        $settings_loaded = array_key_exists("settings", $session);
        $admin_auth_loaded = array_key_exists("is_admin", $session);

        if (!$categories_loaded || $reload_session) {
            $category_service = new CategoryModel();

            $categories = $category_service->getAllCategories([
                "c.*",
                "post_count"
            ]);

            $request->setSession("categories", $categories);
        }

        if (!$settings_loaded || $reload_session) {
            $request->setSession("settings", SettingsModel::getAll());
        }

        if (!$admin_auth_loaded) {
            $request->setSession("is_admin", false);
        }
    }
}
