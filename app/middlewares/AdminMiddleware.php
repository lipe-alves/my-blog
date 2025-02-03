<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

use App\Services\AuthService;

class AdminMiddleware extends Middleware
{
    public function execute(Request $request, Response $response, \Exception $exception = null)
    {
        $session = $request->getSession();
        $get = $request->getGet();

        if (!array_key_exists("admin", $get)) {
            $request->setGet("admin", false);
        }

        if (!array_key_exists("is_admin", $session)) {
            $request->setSession("is_admin", false);
        }
    }
}
