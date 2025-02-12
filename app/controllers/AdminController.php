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
        $get = $this->request->getGet();
        $session = $this->request->getSession();
        $settings = $session["settings"];
        extract($settings);

        if (array_key_exists("location", $get)) {
            $get["location"] = urldecode($get["location"]);
        } else {
            $get["location"] = "/?admin=1";
        }

        if (!str_contains($get["location"], "?")) {
            $get["location"] .= "?";
        }

        if (!str_contains($get["location"], "admin=1")) {
            $get["location"] .= "admin=1";
        }

        $url = parse_url($get["location"]);
        $get["location"] = "$url[path]?$url[query]";
        $get["location"] = str_replace(BASE_URI, "", $get["location"]);
        
        $this->page("admin", [
            "title"       => $blog_name,
            "description" => $blog_catchline,
            "keywords"    => [],
            "location"    => $get["location"]
        ]);
    }

    public function html(Request $request)
    {
        $this->views["index"] = "index";
        $this->views["default"] = "index";

        parent::html($request);
    }
}
