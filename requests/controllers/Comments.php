<?php


class Comments extends Controller
{
    public function new()
    {
        $input = file_get_contents("php://input");

        $json = json_decode($input);

        $comment = new Comment();
        $comment->post_id = $json->postID;
        $comment->user_id = $json->userID;
        $comment->body = $json->body;
        $comment->save();

        // Get the comment and fetch it from the database
        $newComment = Comment::find(["id" => Database::getInstance()->getdbConnection()->lastInsertId()]);

        $newComment->user = $newComment->user();
        $newComment->user->display_pic = Helpers::printIfExternal($newComment->user()->display_pic);
        $newComment->user->name = $newComment->user()->name();
        $newComment->timestamp = Helpers::getTimeSinceMin($newComment->timestamp);

        $data = new stdClass();
        $data->comment = $newComment;

        $this->send(201, $data);
    }

    public function show($postID)
    {
        $post = Post::find(["id" => $postID]);
        $data = new stdClass();

        if ($post) {
            $comments = $post->comments();

            foreach ($comments as $comment) {
                $comment->likesCount = $comment->likesCount();
                $comment->user = $comment->user();
                $comment->user->display_pic = Helpers::printIfExternal($comment->user->display_pic);
                $comment->user->name = $comment->user()->name();
                $comment->timestamp = Helpers::getTimeSinceMin($comment->timestamp);
            }
            $data->comments = $comments;

            $this->send(200, $data);
        } else {
            $data->error = "Post not found";
            $this->send(401, $data);
        }
    }
}