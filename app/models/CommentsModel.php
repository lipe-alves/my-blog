<?php

namespace App\Models;

use App\Core\DatabaseModel;
use App\Exceptions\MissingParamException;
use App\Exceptions\InvalidFormatException;
use App\Exceptions\ResourceNotFoundException;

class CommentsModel extends DatabaseModel
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
            if (str_contains($key, "r.")) {
                $fetch_reader = true;
            } else if (str_contains($key, "p.")) {
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
                    "p.id" => "comm.post_id"
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
        $comments = $this->getComments($columns, ["comm.post_id" => $post_id]);
        return $comments;
    }

    public function createComment(array $data): array|false
    {
        extract($data);

        if (!isset($post_id) || !is_numeric($post_id)) {
            throw new MissingParamException("post_id");
        }

        if (isset($text)) {
            $text = trim($text);
            $text = htmlspecialchars($text);
        }

        if (!isset($text) || !$text) {
            throw new MissingParamException("text");
        }

        if (isset($comment_id) && !is_numeric($comment_id)) {
            throw new InvalidFormatException(
                "comment_id",
                ["numeric"]
            );
        }

        if (isset($comment_id)) {
            $reply_comment = $this->getComment(["id"], ["comment_id" => $comment_id]);
            $comment_found = (bool)$reply_comment;

            if (!$comment_found) {
                throw new ResourceNotFoundException("Comentário de resposta com ID $comment_id não encontrado.");
            }
        }

        if (!isset($reader_email)) {
            throw new MissingParamException("reader_email");
        }

        if (!validate_email($reader_email)) {
            throw new InvalidFormatException(
                "reader_email",
                ["exemplo@example.com"]
            );
        }

        $reader_service = new ReaderModel($this->conn);
        $reader = $reader_service->getReader(["r.id"], ["r.email" => $reader_email]);
        $reader_found = (bool)$reader;
        $reader_id = null;

        if ($reader_found) {
            $reader_id = $reader["id"];

            $reader_service->updateReader($reader_id, [
                "fullname"   => $reader_fullname,
                "first_name" => $reader_first_name,
                "last_name"  => $reader_last_name,
                "email"      => $reader_email,
                "photo"      => $reader_photo,
            ]);
        } else {
            $reader = $reader_service->createReader([
                "fullname"   => $reader_fullname,
                "first_name" => $reader_first_name,
                "last_name"  => $reader_last_name,
                "email"      => $reader_email,
                "photo"      => $reader_photo,
            ]);

            $reader_id = $reader["id"];
        }

        if (!$reader_id) {
            throw new ResourceNotFoundException("Leitor não encontrado.");
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

        $new_comment = $this->getComment(["comm.*"], ["comm.id" => $new_comment_id]);

        return $new_comment;
    }
}
