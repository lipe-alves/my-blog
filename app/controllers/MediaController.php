<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\InternalServerException;
use App\Services\MediaLibraryService;

class MediaController extends Controller {
    private function isFile(string $path)
    {
        return (bool)preg_match("/\.\w+$/", $path);
    }
    
    public function insertFile(Request $request, Response $response)
    {
        $get = $request->getGet();
        $files = $request->getFiles();
        $path = $get["path"];
        

        $response->setStatus(200)->setJson($files)->send();
    }

    public function updateMediaItem(Request $request, Response $response)
    {
        $get = $request->getGet();
        $updates = $request->getPatch();
        $path = $get["path"];
        
        $item = MediaLibraryService::updateMedia($path, $updates);
        if (!isset($item)) throw new InternalServerException();

        $response->setStatus(200)->setJson($item)->send();
    }

    public function deleteMediaItem(Request $request, Response $response)
    {
        $get = $request->getGet();
        $path = $get["path"];

        $is_file = $this->isFile($path);
        $success = false;

        if ($is_file) {
            $success = MediaLibraryService::deleteFile($path);
        } else {
            $success = MediaLibraryService::deleteFolder($path);
        }

        if (!$success) throw new InternalServerException();

        $response->setStatus(200)->setJson([
            "success" => true,
            "message" => $is_file ? "Arquivo excluído com sucesso!" : "Pasta excluída com sucesso!"
        ])->send();
    }
}
