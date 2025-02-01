<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

use App\Services\CategoryService;

use App\Exceptions\InternalServerException;
use App\Exceptions\InvalidInputException;
use App\Exceptions\MissingParamException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\InvalidFormatException;

class CategoriesController extends Controller {
    public function listCategories(Request $request, Response $response) 
    {
        $get = $request->getGet();
        file_put_contents("get.txt", print_r($get, true));
        extract($get);

        if (!isset($columns)) {
            $columns = "c.*";
        }

        $columns = explode(",", $columns);
        $columns = array_map("remove_multiple_whitespaces", $columns);

        $filter_params = [];

        if (isset($category_id)) {
            $filter_params["c.id"] = $category_id;
        }

        if (isset($category_name)) {
            $filter_params["c.name"] = $category_name;
        }

        $categories_service = new CategoryService();
        $categories = $categories_service->getCategories($columns, $filter_params);

        $response->setStatus(200)->setJson($categories)->send();   
    }

    public function insertCategory(Request $request, Response $response)
    {
        $category_data = $request->getPost();
        $categories_service = new CategoryService();

        try {
            $categories_service->startTransaction();

            $last_inserted_comment = $categories_service->createCategory($category_data);
            $success = $last_inserted_comment !== false;
            if (!$success) throw new InternalServerException();

            $categories_service->commit();

            $request->reloadSession();

            $response->setStatus(200)->setJson($last_inserted_comment)->send();
        } catch (\Exception $e) {
            $categories_service->rollback();
            throw $e;
        }
    }

    public function deleteCategory(Request $request, Response $response)
    {
        $params = $request->getParams();
        extract($params);

        $categories_service = new CategoryService();

        try {
            $categories_service->startTransaction();

            $data = [];
            $fetch_field = "id";

            if (is_numeric($id_or_name)) {
                $data["c.id"] = $id_or_name;
            } else {
                $data["c.name"] = $id_or_name;
                $fetch_field = "nome";
            }

            $category = $categories_service->getCategory(["c.id"], $data);
            if (!$category) {
                throw new ResourceNotFoundException("Categoria com $fetch_field igual a \"$id_or_name\" não encontrada");
            }

            $success = $categories_service->deleteCategory($category["id"]);
            if (!$success) throw new InternalServerException();

            $categories_service->commit();

            $request->reloadSession();

            $response->setStatus(200)->setJson([
                "success" => true,
                "message" => "Categoria excluída com sucesso!"
            ])->send();
        } catch (\Exception $e) {
            $categories_service->rollback();
            throw $e;
        }
    }
}
