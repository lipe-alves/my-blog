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

        if (!array_key_exists("admin", $get)) {
            $get["admin"] = false;
        }
        
        extract($get);

        if (!isset($adm) || !isset($password)) {
            return $request->setSession("is_authenticated", false);
        }

        $adm = (bool)$adm;
        if (!$adm) {
            return $request->setSession("is_authenticated", false);
        }
        
        $passwords_match = AuthService::verifyPassword($password, $settings["adm_password"]);
        if (!$passwords_match) {
            return $request->setSession("is_authenticated", false);
        }

        $request->setSession("is_authenticated", true);
    }
}
