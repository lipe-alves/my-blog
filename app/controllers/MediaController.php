<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\InternalServerException;
use App\Services\MediaLibraryService;

class MediaController extends Controller {
    public function insertFile(Request $request, Response $response)
    {
        $get = $request->getGet();
        $files = $request->getFiles();
        $path = $get["path"];
        

        $response->setStatus(200)->setJson($files)->send();
    }

    public function updateFile(Request $request, Response $response) 
    {
        $get = $request->getGet();
        $patch = $request->getPatch();
        $path = $get["path"];

        $file = MediaLibraryService::updateFile($path, $patch);

        $response->setStatus(200)->setJson($file)->send();
    }

    public function deleteFile(Request $request, Response $response) 
    {
        $get = $request->getGet();
        $patch = $request->getPatch();
        $path = $get["path"];

        $success = MediaLibraryService::deleteFile($path, $patch);
        if (!$success) throw new InternalServerException();

        $response->setStatus(200)->setJson([
            "success" => true,
            "message" => "Arquivo excluído com sucesso!"
        ])->send();
    }
}
