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

        $comment_data = [
            "post_id"           => $post["post_id"],
            "comment_id"        => $post["reply_to"],
            "text"              => $post["comment"],
            "reader_fullname"   => $post["fullname"],
            "reader_first_name" => $post["first_name"],
            "reader_last_name"  => $post["last_name"],
            "reader_email"      => $post["email"],
            "reader_photo"      => $post["photo"],
        ];

        try {
            $comments_service->startTransaction();

            $last_inserted_id = $comments_service->createComment($comment_data);

            $last_inserted_comment = $comments_service->getComment(["*"], ["comment_id" => $last_inserted_id]);
            $success = $last_inserted_comment !== false;
            if (!$success) throw new InternalServerException();

            $comments_service->commit();

            $response->setStatus(200)->setJson($last_inserted_comment)->send();
        } catch (\Exception $e) {
            $comments_service->rollback();
            throw $e;
        }
    }
}
