<?php

namespace App\Controllers;

use App\Services\CommentsService;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\InternalServerException;

class CommentsController extends Controller
{
    public function listComments() {}

    public function insertComment(Request $request, Response $response)
    {
        $post = $request->getPost();

        $comments_service = new CommentsService();

        $post = array_merge([
            "post_id"    => null,
            "reply_to"   => null,
            "comment"    => null,
            "fullname"   => null,
            "first_name" => null,
            "last_name"  => null,
            "email"      => null,
            "photo"      => null,
        ], $post);

        $new_keys = [
            "post_id"           => "post_id",
            "comment_id"        => "reply_to",
            "text"              => "comment",
            "reader_fullname"   => "fullname",
            "reader_first_name" => "first_name",
            "reader_last_name"  => "last_name",
            "reader_email"      => "email",
            "reader_photo"      => "photo"
        ];
        $comment_data = array_map(function ($old_key) use ($post) {
            return $post[$old_key];
        }, $new_keys);

        try {
            $comments_service->startTransaction();

            $last_inserted_id = $comments_service->createComment($comment_data);
            $success = $last_inserted_id !== false;
            if (!$success) throw new InternalServerException();

            $last_inserted_comment = $comments_service->getComment(["*"], ["comment_id" => $last_inserted_id]);
            $success = $last_inserted_comment !== null;
            if (!$success) throw new InternalServerException();

            $comments_service->commit();

            $response->setStatus(200)->setJson($last_inserted_comment)->send();
        } catch (\Exception $e) {
            $comments_service->rollback();
            throw $e;
        }
    }
}
