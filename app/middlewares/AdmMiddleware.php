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
            return $request->setSession("admin", false);
        }

        $adm = (bool)$adm;
        if (!$adm) {
            return $request->setSession("admin", false);
        }
        
        $passwords_match = AuthService::verifyPassword($password, $settings["adm_password"]);
        if (!$passwords_match) {
            return $request->setSession("admin", false);
        }

        $request->setSession("admin", true);
    }
}
