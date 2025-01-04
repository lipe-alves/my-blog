<?php

namespace App\Services;

use App\Core\DatabaseConnection;

class CommentsService
{
    public static function getComments(array $columns, array $data) {}

    public static function getPostComments(string $post_id, array $columns = ["*"]): array
    {
        $conn = DatabaseConnection::create();
        $columns = implode(", ", $columns);
        $comments = $conn->select("SELECT $columns FROM Comment WHERE post_id = :post_id", ["post_id" => $post_id]);
        return $comments;
    }

    public static function createComment(array $data)
    {
        extract($data);

        $conn = DatabaseConnection::create();

        if (!isset($post_id) || !is_numeric(!$post_id)) {
            throw new \Exception("Valor do ID do post não é válido.");
        }

        if (isset($text)) {
            $text = trim($text);
        }

        if (!isset($text) || !$text) {
            throw new \Exception('Campo "texto" é obrigatório.');
        }
    }
}
