<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

class ComponentsController extends Controller
{
    public array $views = [
        "post-filters" => "postFilters"
    ];

    public function postFilters()
    {
        $get = $this->request->getGet();
        $this->component("filters", ["query" => $get]);
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
