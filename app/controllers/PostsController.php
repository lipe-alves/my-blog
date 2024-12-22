<?php

namespace App\Controllers;

use App\Services\PostService;
use App\Exceptions\MissingParamException;
use App\Exceptions\InvalidParamException;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class PostsController extends Controller
{
    public function listPosts(Request $request, Response $response)
    {
        extract($request->getGet());

        if (!isset($page)) {
            throw new MissingParamException("page");
        }

        if (!isset($size)) {
            throw new MissingParamException("size");
        }

        if (!is_numeric($page)) {
            throw new InvalidParamException("page", ["int"]);
        }

        if (!is_numeric($size)) {
            throw new InvalidParamException("size", ["int"]);
        }

        if (!isset($columns)) {
            $columns = "p.*";
        }

        $columns = explode(",", $columns);
        $columns = array_map("trim", $columns);

        $page = (int)$page;
        $size = (int)$size;

        $limit = $size + $size * $page;
        $offset = $limit - $size;
        $offset = max($offset, 0);
        $offset = min($offset, $limit);

        $filter_params = [
            "offset" => $offset,
            "limit" => $limit
        ];

        if (isset($category)) {
            if (is_numeric($category)) {
                $filter_params["category_id"] = $category;
            } else {
                $filter_params["category_name"] = $category;
            }
        }

        $posts = PostService::getPosts($columns, $filter_params);

        $response->setStatus(200)->setJson($posts)->send();
    }

    public function index() {}
}
