<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ApiException;

class ErrorMiddleware extends Middleware
{
    public function execute(Request $request, Response $response, \Exception $exception = null)
    {
        $has_error = isset($exception) && (bool)$exception;
        if (!$has_error) return;

        if (!($exception instanceof ApiException)) {
            $exception = new ApiException(
                $exception->getMessage(),
                500,
                []
            );
        }

        $status = $exception->getStatus();
        $message = $exception->getMessage();
        $data = $exception->getData();

        $response->setStatus($status)->setJson([
            "success" => false,
            "message" => $message,
            "data"    => $data
        ])->send();
    }
}
