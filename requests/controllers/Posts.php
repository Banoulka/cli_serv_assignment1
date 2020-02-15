<?php


class Posts extends Controller
{
    public function trending($offset) {
        // Get all the posts using the old posts model method
        $posts = Post::trending($offset);

        // Looping through the posts, change all the methods
        // into properties as methods dont send
        foreach ($posts as $post) {
            $post->user = $post->user();
            $post->watchCount = $post->watchCount();
            $post->likeCount = $post->likesCount();
            $post->commentCount = $post->commentCount();
            $post->time = Helpers::getTimeSince($post->time);
            $post->tags = $post->tags();
            $post->title = substr($post->title, 0, 33);
        }
        $data = new stdClass();
        $data->posts = $posts;

        // Sending the posts into an empty data object.
        $this->send(200, $data);
    }
}