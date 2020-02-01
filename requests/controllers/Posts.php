<?php


class Posts extends Controller
{
    public function trending($offset) {
        $posts = Post::trending($offset);
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
        $this->send(200, $data);
    }
}