<?php

namespace App\Controllers;

use App\Core\Services\PostsService;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class PostsController extends Controller
{
    public function listPosts(Request $request, Response $response) {


        $response->setStatus(200)->setJson([])->send();
    }

    public function index()
    {
    }
}
