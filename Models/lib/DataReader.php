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

    public function randomizePostTime($numberPosts = -1)
    {
        $posts = Post::all($numberPosts);
        foreach ($posts as $post)
        {
            // Randomise time
            $now = new DateTime();
            $newTime = rand(666666666, $now->getTimestamp());
            $sql = "UPDATE posts SET time = $newTime WHERE id = $post->id";
            Database::getInstance()->getdbConnection()->query($sql);
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

    public function randomisePostLikesBetterAlgorithim(int $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $post = Post::random(1);
            $randomUsers = User::random(rand(5, 30));

            $sql = "";
            foreach ($randomUsers as $user) {
                $sql .= "\nINSERT INTO post_likes VALUES ($user->id, $post->id); ";
            }
            Database::getInstance()->getdbConnection()->exec($sql);
            echo "Successfully liked post $post->id " . count($randomUsers) . " times <br/>";
        }
    }

    public function randomisePostWatches($postNumber = -1)
    {
        $posts = Post::all($postNumber);
        $users = User::all();

        foreach ($posts as $post) {
            // Randomise watches
            for ($i = 0; $i < rand(0, 500); $i++) {
                $index = rand(0, count($users)-1);
                $chosenUser = $users[$index];

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

    public function randomiseFollowersBetterAlgorithm(int $number) {
        $followCount = 0;
        for ($i = 0; $i < $number; $i++) {
//            841 - 44268
            $randomUserOne = User::find(["id" => rand(841, 44268)]);
            $randomUserTwo = User::find(["id" => rand(841, 44268)]);
            if ($randomUserOne && $randomUserTwo && !$randomUserOne->isFollower($randomUserTwo)) {
                $randomUserTwo->followUser($randomUserOne);
                $followCount++;
            }
        }
        echo "Successfully followed -> $followCount <br/>";
    }

    public function randomisePostTags ($postNumber = -1)
    {
        // Purge tags
        $sql = "DELETE FROM post_tags";
        Database::getInstance()->getdbConnection()->query($sql);

        $posts = Post::all($postNumber);
        foreach ($posts as $post) {
            $tags = Tag::all();
            $chosenTags = [];
            // Random number of tags
            for ($i = 0; $i < rand(2, 5); $i++) {
                // Add post tag
                $index = rand(0, count($tags)-1);
                array_push($chosenTags, $tags[$index]->id);
                array_splice($tags, $index, 1);
            }
            // Prepare the sql statements
            $sql = "";
            foreach ($chosenTags as $tag) {
                $sql .= "\nINSERT into post_tags VALUES ($post->id, $tag);";
            }
            Database::getInstance()->getdbConnection()->exec($sql);
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

