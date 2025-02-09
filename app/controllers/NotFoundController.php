<?php

namespace App\Controllers;

use App\Core\Controller;

class NotFoundController extends Controller
{
    public function index()
    {
        $this->page("404", [
            "layout" => "base-page",
            "title"  => "Página não encontrada"
        ]);
    }
}
