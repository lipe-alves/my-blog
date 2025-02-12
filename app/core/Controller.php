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

    private function moveStylesheetsToHead(string $html)
    {
        preg_match_all("/<link[^>]*>/i", $html, $matches);

        foreach ($matches[0] as $link) {
            $html = str_replace($link, "", $html);
            $html = str_replace("</head>", "$link\n</head>", $html);
        }
        
        return $html;
    }

    private function moveScriptsToBodyEnd(string $html)
    {
        preg_match_all("/<script[^>]*>[^<]*<\/script>/i", $html, $matches);

        foreach ($matches[0] as $script) {
            $html = str_replace($script, "", $html);
            $html = str_replace("</body>", "$script\n</body>", $html);
        }
        
        return $html;
    }

    private function removeHtmlComments(string $html)
    {
        return preg_replace("/<!--.*?-->/", "", $html);
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

        if (str_contains($html, "</head>")) {
            $html = $this->moveStylesheetsToHead($html);
        }

        if (str_contains($html, "</body>")) {
            $html = $this->moveScriptsToBodyEnd($html);
        }

        $html = $this->removeHtmlComments($html);

        $this->response->setStatus(200)->setHtml($html)->send();
    }

    protected function view(string $view, array $data = [])
    {
        $this->showHtml("pages/$view", $data);
    }

    protected function page(string $page, array $data = [])
    {
        extract($data);

        if (isset($layout)) {
            $this->layout($layout, array_merge([
                "page" => $page,
            ], $data));
        } else {
            $this->view($page, $data);
        }
    }

    protected function component(string $component, array $data = [])
    {
        $this->showHtml("components/$component", $data);
    }

    protected function layout(string $layout, array $data = [])
    {
        $this->showHtml("layouts/$layout", $data);
    }
}
