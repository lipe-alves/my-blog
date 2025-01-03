<?php

namespace App\Services;

use App\Core\DatabaseConnection;

class CommentsService
{
    public static function getPostComments(string $post_id, array $columns = ["*"]): array
    {
        $conn = DatabaseConnection::create();
        $columns = implode(", ", $columns);
        $comments = $conn->select("SELECT $columns FROM Comment WHERE post_id = :post_id", ["post_id" => $post_id]);
        return $comments;
    }
}
