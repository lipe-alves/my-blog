<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class ComponentsController extends Controller
{
    public array $views = [
        "post-filters"  => "postFilters",
        "header"        => "header",
        "media-library" => "mediaLibrary"
    ];

    public function postFilters()
    {
        $get = $this->request->getGet();
        $this->component("filters", ["query" => $get]);
    }

    public function header() {
        $this->component("header", []);
    }

    public function mediaLibrary() {
        $get = $this->request->getGet();
        $params = [];

        if (array_key_exists("base_path", $get)) {
            $params["base_path"] = $get["base_path"];
        }

        if (array_key_exists("mime_type", $get)) {
            $params["mime_type"] = explode(",", $get["mime_type"]);
            $params["mime_type"] = array_map("trim", $params["mime_type"]);
        }

        if (array_key_exists("on_select", $get)) {
            $params["on_select"] = $get["on_select"];
        }

        $this->component("media-library", $params);
    }

    public function html(Request $request) 
    {
        $get = $request->getGet();
        
        if (!array_key_exists("view", $get)) {
            $get["view"] = "index";
        }
        
        $view = $get["view"];

        foreach ($this->views as $view_name => $view_method) {
            if ($view_name === $view) {
                return $this->{$view_method}();
            }
        }

        if (array_key_exists("default", $this->views)) {
            $view_method = $this->views["default"];
            $this->{$view_method}();
        }
    }
}
