<?php

namespace App\Controllers;

use App\Core\Controller;

class ComponentsController extends Controller
{
    public function postFilters()
    {
        $get = $this->request->getGet();
        $this->component("filters", ["query" => $get]);
    }
}
