<?php

namespace App\Controllers;

use App\Core\Services\CommentsService;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class CommentsController extends Controller
{
    public function listComments() {}

    public function insertComment(Request $request, Response $response)
    {
        $post = $request->getPost();
        extract($post);

        $response->setStatus(200)->setJson($post)->send();
    }
}
