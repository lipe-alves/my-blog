<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class TestsController extends Controller
{
    public function ping(Request $request, Response $response)
    {
        $response->setStatus(200)->setJson([
            "path"    => $request->getPath(),
            "headers" => $request->getHeader(),
            "post"    => $request->getPost(),
            "get"     => $request->getGet(),
            "params"  => $request->getParams(),
            "method"  => $request->getMethod(),
            "date"    => date("D, d M Y H:i:s")
        ])->send();
    }

    public function removeAccents(Request $request, Response $response)
    {
        $post = $request->getPost();
        $original = $post["text"];
        $result = remove_accents($original);

        $response->setStatus(200)->setJson([
            "original" => $original,
            "result"   => $result
        ])->send();
    }
}
