<?php

namespace App\Services;

use App\Core\DatabaseService;

class CommentsService extends DatabaseService
{
    public function getComments(array $columns, array $data)
    {
        $fetch_reader = false;
        $fetch_post = false;

        foreach ($columns as $column) {
            if (str_contains($column, "r.")) {
                $fetch_reader = true;
            } elseif (str_contains($column, "p.")) {
                $fetch_post = true;
            }
        }

        foreach ($data as $key => $value) {
            if (str_contains($key, "reader")) {
                $fetch_reader = true;
            } else if (str_contains($key, "post")) {
                $fetch_post = true;
            }
        }

        if ($fetch_reader) {
            if (!array_key_exists("join", $data)) {
                $data["join"] = [];
            }

            $data["join"][] = [
                "type"       => "LEFT",
                "table"      => "Reader",
                "conditions" => [
                    "comm.reader_id" => "r.id"
                ]
            ];
        }

        if ($fetch_post) {
            if (!array_key_exists("join", $data)) {
                $data["join"] = [];
            }

            $data["join"][] = [
                "type"       => "LEFT",
                "table"      => "Post",
                "conditions" => [
                    "comm.post_id" => "p.id"
                ]
            ];
        }

        $data["table"] = "Comment";

        $comments = $this->select($columns, $data);

        return $comments;
    }

    public function getComment(array $columns, array $data)
    {
        $comments = $this->getComments($columns, $data);
        return count($comments) === 0 ? null : $comments[0];
    }

    public function getPostComments(string $post_id, array $columns = ["*"]): array
    {
        $comments = $this->getComments($columns, ["post_id" => $post_id]);
        return $comments;
    }

    public function createComment(array $data): string|false
    {
        extract($data);

        if (!isset($post_id) || !is_numeric(!$post_id)) {
            throw new \Exception("Valor do ID do post não é válido.");
        }

        if (isset($text)) {
            $text = trim($text);
        }

        if (!isset($text) || !$text) {
            throw new \Exception('Campo "texto" é obrigatório.');
        }

        if (isset($comment_id) && !is_numeric($comment_id)) {
            throw new \Exception("Valor do ID do comentário a responder não é válido.");
        }

        $reply_comment = $this->getComment(["id"], ["comm.id" => $comment_id]);
        $comment_found = (bool)$reply_comment;
        if (!$comment_found) {
            throw new \Exception("Comentário de resposta com ID $comment_id não encontrado.");
        }

        if (!isset($reader_email)) {
            throw new \Exception('Campo "email" é obrigatório.');
        }

        if (!validate_email($reader_email)) {
            throw new \Exception('Campo "email" inválido.');
        }

        $reader_service = new ReaderService($this->conn);
        $reader = $reader_service->getReader(["id"], ["r.email" => $reader_email]);
        $reader_found = (bool)$reader;
        $reader_id = null;

        if ($reader_found) {
            $reader_id = $reader["id"];
        } else {
            $reader_id = $reader_service->createReader([
                "fullname"   => $reader_fullname,
                "first_name" => $reader_first_name,
                "last_name"  => $reader_last_name,
                "email"      => $reader_email,
                "photo"      => $reader_photo,
            ]);
        }

        if (!$reader_id) {
            throw new \Exception("Leitor não encontrado.");
        }

        $new_comment_id = $this->insert("Comment", [
            [
                "post_id"    => $post_id,
                "reader_id"  => $reader_id,
                "comment_id" => $comment_id,
                "text"       => $text
            ]
        ]);

        $success = $new_comment_id !== false;
        if (!$success) return false;

        return $new_comment_id;
    }
}
