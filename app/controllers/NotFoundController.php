<?php

namespace App\Controllers;

use App\Core\Controller;

class NotFoundController extends Controller
{
    public function index()
    {
        $this->page("404", [
            "title"       => "Página não encontrada",
            "description" => "Página não encontrada, volte à inicial",
            "keywords"    => []
        ]);
    }
}
