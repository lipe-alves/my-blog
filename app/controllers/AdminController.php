<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\SettingsService;
use App\Services\AuthService;
use App\Services\AdminService;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\MissingParamException;

class AdminController extends Controller
{
    public function authenticate(Request $request, Response $response)
    {
        $post = $request->getPost();
        extract($post);

        if (!isset($password) || !$password) {
            throw new MissingParamException("password");
        }

        $passwords_match = AdminService::authenticate($password);

        if (!$passwords_match) {
            return $response->setStatus(200)->setJson([
                "success" => false,
                "message" => "Senha inválida!"
            ])->send();
        }

        $response->setStatus(200)->setJson([
            "success" => true,
            "message" => "Autenticado com sucesso!"
        ])->send();
    }

    // Views

    public function index() 
    {
        $session = $this->request->getSession();
        $settings = $session["settings"];
        extract($settings);
        
        $this->view("admin", [
            "title"       => $blog_name,
            "description" => $blog_catchline,
            "keywords"    => []
        ]);
    }

    public function page(Request $request)
    {
        $get = $request->getGet();
        $view = "index";

        if (array_key_exists("view", $get)) {
            $view = $get["view"];
        }

        switch ($view) {
            case "index":
            default:
                return $this->index();
        }
    }
}
