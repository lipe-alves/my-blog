<?php

namespace App\Controllers;

use App\Services\CategoryService;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\InternalServerException;
use App\Exceptions\ResourceNotFoundException;

class CategoriesController extends Controller {
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
                throw new ResourceNotFoundException("Categoria com $fetch_field igual a \"$id\" não encontrada");
            }

            $success = $categories_service->deleteCategory($category["id"]);
            if (!$success) throw new InternalServerException();

            $categories_service->commit();

            $response->setStatus(200)->setJson($last_inserted_comment)->send();
        } catch (\Exception $e) {
            $categories_service->rollback();
            throw $e;
        }
    }
}
