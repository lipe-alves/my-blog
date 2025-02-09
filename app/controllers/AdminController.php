<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AdminService;
use App\Exceptions\MissingParamException;

class AdminController extends ComponentsController
{
    public function login(Request $request, Response $response)
    {
        $post = $request->getPost();
        extract($post);

        if (!isset($password) || !$password) {
            throw new MissingParamException('"senha"');
        }

        $passwords_match = AdminService::authenticate($password);

        if (!$passwords_match) {
            return $response->setStatus(400)->setJson([
                "success" => false,
                "message" => "Senha inválida!"
            ])->send();
        }
        
        $request->setSession("is_admin", true);
        $request->reloadSession();

        $response->setStatus(200)->setJson([
            "success" => true,
            "message" => "Autenticado com sucesso!"
        ])->send();
    }

    public function logout(Request $request, Response $response)
    {
        $request->setSession("is_admin", false);
        $request->reloadSession();

        $response->setStatus(200)->setJson([
            "success" => true,
            "message" => "Sessão finalizada com sucesso!"
        ])->send();
    }

    // Views

    public function index() 
    {
        $session = $this->request->getSession();
        $settings = $session["settings"];
        extract($settings);
        
        $this->page("admin", [
            "title"       => $blog_name,
            "description" => $blog_catchline,
            "keywords"    => []
        ]);
    }

    public function html(Request $request)
    {
        $this->views["index"] = "index";
        $this->views["default"] = "index";

        parent::html($request);
    }
}
