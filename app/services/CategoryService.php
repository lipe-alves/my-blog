<?php

namespace App\Services;

use App\Core\DatabaseService;
use App\Exceptions\ResourceNotFoundException;

class CategoryService extends DatabaseService
{
    public function getCategories(array $columns, array $data)
    {
        foreach ($columns as $i => $column) {
            if ($column === "post_count") {
                $columns[$i] = "(SELECT COUNT(DISTINCT pc.post_id) FROM Post_x_Category pc WHERE pc.category_id = c.id) AS post_count";
            }
        }

        $data["table"] = "Category";

        $categories = $this->select($columns, $data);
        return $categories;
    }

    public function getCategory(array $columns, array $data)
    {
        $categories = $this->getCategories($columns, $data);
        return count($categories) === 0 ? null : $categories[0];
    }

    public function getCategoryById(string $id, array $columns = ["*"])
    {
        $category = $this->getCategory($columns, ["c.id" => $id]);
        return $category;
    }

    public function getCategoryByName(string $name, array $columns = ["*"])
    {
        $category = $this->getCategory($columns, ["c.name" => $name]);
        return $category;
    }

    public function getAllCategories(array $columns = ["*"], $limit = null)
    {
        $data = [];
        if ($limit !== null) {
            $data["limit"] = $limit;
        }

        $categories = $this->getCategories($columns, $data);
        return $categories;
    }

    public function deleteCategory(string $id): bool
    {
        $category = $this->getCategoryById($id, ["c.id"]);
        if (!$category) {
            throw new ResourceNotFoundException('Categoria com id igual a "'.$id.'" não encontrada');
        }

        $success = $this->delete("Category", ["c.id" => $id]);
        return $success;
    }
}
