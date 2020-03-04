<?php


class Posts extends Controller
{
    public function trending($offset) {
        // Get all the posts using the old posts model method
        $posts = Post::trending($offset);

        $data = new stdClass();
        $data->posts = $posts;

        // Sending the posts into an empty data object.
        $this->send(200, $data);
    }
}