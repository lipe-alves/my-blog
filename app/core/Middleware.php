<?php

namespace App\Core;

use App\Core\Request;
use App\Core\Response;

abstract class Middleware
{
    abstract function execute(Request $request, Response $response, \Exception $exception = null);
}
