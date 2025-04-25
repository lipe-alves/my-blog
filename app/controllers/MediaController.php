<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\InternalServerException;
use App\Exceptions\MissingParamException;
use App\Models\MediaLibraryModel;

class MediaController extends Controller {
    private function isFile(string $path)
    {
        return (bool)preg_match("/\.\w+$/", $path);
    }

    private function validatePath() 
    {
        $get = $this->request->getGet();
        if (!isset($get["path"])) {
            throw new MissingParamException('"caminho"');
        }

        $path = $get["path"];
        if (!$path) {
            throw new MissingParamException('"caminho"');
        }
    }
    
    public function insertMediaItem(Request $request, Response $response)
    {
        $this->validatePath();

        $get = $request->getGet();
        $post = $request->getPost();
        $form_files = $request->getFiles();
        $path = $get["path"];
        $type = $get["type"];
        $params = [];
       
        if ($type === "file") {
            $form_files = $form_files["files"];
            $n_files = count($form_files["name"]);
            $files = [];
            
            for ($i = 0; $i < $n_files; $i++) {
                $file = [
                    "name" => $form_files["name"][$i],
                    "type" => $form_files["type"][$i],
                    "tmp_name" => $form_files["tmp_name"][$i],
                    "error" => $form_files["error"][$i],
                    "size" => $form_files["size"][$i],
                ];
    
                $files[] = $file;
            }

            $params = array_merge($post, ["files" => $files]);
        } else {
            $params = $post;
        }

        $results = MediaLibraryModel::createMedia($path, $type, $params);

        $response->setStatus(200)->setJson($results)->send();
    }

    public function updateMediaItem(Request $request, Response $response)
    {
        $this->validatePath();

        $get = $request->getGet();
        $updates = $request->getPatch();
        $path = $get["path"];
        
        $item = MediaLibraryModel::updateMedia($path, $updates);
        if (!isset($item)) throw new InternalServerException();

        $response->setStatus(200)->setJson($item)->send();
    }

    public function deleteMediaItem(Request $request, Response $response)
    {
        $this->validatePath();

        $get = $request->getGet();
        $path = $get["path"];

        $is_file = $this->isFile($path);
        $success = MediaLibraryModel::deleteMedia($path);
        if (!$success) throw new InternalServerException();

        $response->setStatus(200)->setJson([
            "success" => true,
            "message" => $is_file ? "Arquivo excluído com sucesso!" : "Pasta excluída com sucesso!"
        ])->send();
    }
}
