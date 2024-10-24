<?php

namespace App\Controllers;

use App\Core\Controller;

class NotFoundController extends Controller
{
    public function index()
    {
        $this->view("404", [
            "title" => "Página não encontrada"
        ]);
    }
}
