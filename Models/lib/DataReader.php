<?php

require_once "Models/User.php";
require_once "Models/Post.php";

class DataReader
{
    public function __construct()
    {

    }

    public function readUserData()
    {
        $userFile = fopen("user_data.txt", "r") or die("could not find file");
        if ($userFile) {
            while (!feof($userFile)) {
                $line = fgets($userFile);
                $dataObjects = explode(",", $line);
                $user = new User();
                $user->first_name = $dataObjects[0];
                $user->last_name = $dataObjects[1];
                $user->email = $dataObjects[2];
                $user->display_name = $dataObjects[3];
                $user->display_pic = $dataObjects[4];
                $user->password = $dataObjects[5];
                $user->bio = $dataObjects[6] != "\n" ? $dataObjects[6] : null;
                $user->save();
            }
            fclose($userFile);
        }
        echo "success!";
        die();
    }

    public function readPostData()
    {
        $userFile = fopen("post_data.txt", "r") or die("could not find file");
        if ($userFile) {
            while (!feof($userFile)) {
                $line = fgets($userFile);
                $data = explode("#", $line);
                $post = new Post();
                $post->user_id = $data[0];
                $post->title= $data[1];
                $post->description = $data[2];
                $post->body = $data[3];
                $post->cover_image = $data[4];
                $post->type_stage = $data[5];
                $post->save();
            }
        }
        fclose($userFile);
    }

    public function randomizePostTime()
    {
        $posts = Post::all();
        foreach ($posts as $post)
        {
            // Randomise time
            $now = new DateTime();
            $newTime = rand(1262055681, $now->getTimestamp());
            $post->time = $newTime;
            $post->save();

        }
    }

    public function randomisePostLikes()
    {
        $posts = Post::all();

        foreach ($posts as $post) {
            // Randomise likes
            $users = User::all();

            for ($i = 0; $i < rand(0, 200); $i++) {
                $index = rand(0, count($users)-1);
                $chosenUser = $users[$index];
                // remove user from array
                unset($users[$index]);
                $users = array_values($users);
                if (!$chosenUser->isLiked($post->id))
                {
                    $chosenUser->likePost($post->id);
                }
            }
        }
    }

    public function randomisePostWatches()
    {
        $posts = Post::all();

        foreach ($posts as $post) {
            // Randomise likes
            $users = User::all();
            for ($i = 0; $i < rand(0, 80); $i++) {
                $index = rand(0, count($users)-1);
                $chosenUser = $users[$index];

                unset($users[$index]);
                $users = array_values($users);

                if (!$chosenUser->isOnWatchList($post->id)){
                    $chosenUser->addToWatchList($post->id);
                }
            }
        }
    }

    public function randomiseFollowers()
    {
        $users = User::all();
        foreach ($users as $user)
        {
            $availableUsers = User::all();
            for ($i = 0; $i < rand(0, 200); $i++) {
                $chosenIndex = rand(0, count($availableUsers)-1);
                $chosenUser = $availableUsers[$chosenIndex];
                if (!$chosenUser->isFollower($user)) {
                    $chosenUser->followUser($user);
                }

                unset($availableUsers[$chosenIndex]);
                $availableUsers = array_values($availableUsers);
            }
        }
    }

    public function randomisePostTags ()
    {
        // Purge tags
        $sql = "DELETE FROM post_tags";
        Database::getInstance()->getdbConnection()->query($sql);

        $posts = Post::all();
        $tags = Tag::all();
        foreach ($posts as $post) {
            $chosenTags = [];
            for ($i = 0; $i < rand(2, 5); $i++) {
                // Add post tag
                $chosenTags[$i] = $tags[rand(0, count($tags)-1)]->title;
            }
            $post->addTags($chosenTags);
        }
    }

    public function randomisePostComments ()
    {
        // Purge comments
        $sql = "DELETE FROM post_comments";
        Database::getInstance()->getdbConnection()->query($sql);

        $posts = Post::all();
        $users = User::all();

        foreach ($posts as $post) {
            $noComments = rand(0, $post->likesCount());
            for ($i = 0; $i < $noComments; $i++) {
                $comment = new Comment();
                $comment->post_id = $post->id;
                $comment->user_id = $users[rand(0, count($users)-1)]->id;
                $comment->body = "Quisque tincidunt turpis mi. Maecenas pulvinar, sapien nec cursus posuere, ipsum nulla finibus nisi, et vulputate nulla enim quis nulla. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum iaculis magna odio, sed lacinia orci congue a.";
                $comment->save();
            }
        }
    }
}

