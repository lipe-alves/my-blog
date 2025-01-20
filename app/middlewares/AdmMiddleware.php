<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

use App\Services\AuthService;

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

        $adm = (bool)$adm;
        if (!$adm) {
            return;
        }
        
        $passwords_match = AuthService::verifyPassword($password, $settings["adm_password"]);
        if (!$passwords_match) {
            return;
        }

        $request->setSession("admin", true);
    }
}
