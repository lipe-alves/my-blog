<?php

namespace App\Core;

class Controller
{
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

        require_once $phtml_path;
    }

    protected function view(string $view, array $data = [])
    {
        $this->showHtml("pages/$view", $data);
    }

    protected function component(string $component, array $data = [])
    {
        $this->showHtml("components/$component", $data);
    }
}
