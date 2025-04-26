<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

class ComponentsController extends Controller
{
    public array $views = [
        "post-filters" => "postFilters",
        "header" => "header",
        "media-library" => "mediaLibrary",
        "admin-actions" => "adminActions"
    ];

    public function adminActions(): void
    {
        $this->component("admin-actions");
    }

    public function postFilters(): void
    {
        $get = $this->request->getGet();
        $this->component("filters", ["query" => $get]);
    }

    public function header()
    {
        $this->component("header", []);
    }

    public function mediaLibrary(): void
    {
        $get = $this->request->getGet();
        $params = array_merge([
            "base_path" => "/",
            "accept" => "*",
            "multiple" => "true"
        ], $get);

        $params["multiple"] = $params["multiple"] === "true" || $params["multiple"] === "1";

        $this->component("media-library", $params);
    }

    public function html(Request $request): void
    {
        $get = $request->getGet();

        if (!array_key_exists("view", $get)) {
            $get["view"] = "index";
        }

        $view = $get["view"];

        foreach ($this->views as $view_name => $view_method) {
            if ($view_name === $view) {
                $this->{$view_method}();
                return;
            }
        }

        if (array_key_exists("default", $this->views)) {
            $view_method = $this->views["default"];
            $this->{$view_method}();
        }
    }
}
