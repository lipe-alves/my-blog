<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

use App\Services\CategoryService;
use App\Services\SettingsService;

class AdmMiddleware extends Middleware
{
    public function execute(Request $request, Response $response, \Exception $exception = null)
    {
        $session = $request->getSession();
        $settings = $session["settings"];
        $get = $request->getGet();
        extract($get);

        if (!isset($adm) || !isset($password)) {
            return;
        }
        
        if (!$adm) {
            return;
        }

        
    }
}
