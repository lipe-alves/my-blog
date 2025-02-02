<?php

namespace App\Core;

use App\Core\Request;
use App\Core\Response;

class Controller
{
    public Request $request;
    public Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    private function showHtml(string $path, array $data = [])
    {
        extract($data);

        $phtml_path = ROOT_PATH . "/app/views/{$path}.phtml";

        if (!file_exists($phtml_path)) {
            $phtml_path = ROOT_PATH . "/app/views/{$path}/index.phtml";
        }

        if (!file_exists($phtml_path)) {
            throw new \Exception("$path não existe");
        }

        ob_start();
        require $phtml_path;

        $html = ob_get_clean();

        $this->response->setStatus(200)->setHtml($html)->send();
    }

    protected function view(string $view, array $data = [])
    {
        $this->showHtml("pages/$view", $data);
    }

    protected function component(string $component, array $data = [])
    {
        $this->showHtml("components/$component", $data);
    }

    protected function page(string $page, array $data = [])
    {
        ob_start();

        $this->view($page, $data);

        $page_html = ob_get_clean();

        $data["page_html"] = $page_html;

        $this->component("base-page", $data);
    }
}
