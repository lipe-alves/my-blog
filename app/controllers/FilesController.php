<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class FilesController extends Controller {
    public function insertFile(Request $request, Response $response)
    {
        $files = $request->getFiles();
        $response->setStatus(200)->setJson($files)->send();
    }
}