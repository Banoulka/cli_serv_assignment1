<?php

require_once "Models/User.php";
require_once "Models/Post.php";

class DataReader
{
    private $_dbHandler;

    public function __construct()
    {
        $this->_dbHandler = Database::getInstance()->getdbConnection();
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
            $this->_dbHandler->query($sql);
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
        $sqlPosts = "SELECT posts.*, COUNT(post_likes.user_id) as Likes
                    FROM posts LEFT JOIN post_likes ON posts.id = post_likes.post_id
                    GROUP BY posts.id
                    ORDER BY Likes
                    LIMIT $number;";
        $posts = $this->_dbHandler->query($sqlPosts)->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Post");

        foreach ($posts as $post) {
            $sql = "";
            $randomUsers = User::random(rand(5, 400));
            foreach ($randomUsers as $user) {
                $sql .= "\nINSERT INTO post_likes VALUES ($user->id, $post->id); ";
            }
            $this->_dbHandler->exec($sql);
//            echo "Successfully liked post $post->id " . count($randomUsers) . " times <br/>";
        }
    }

    public function randomisePostWatches($postNumber = 100)
    {
        $sqlPosts = "SELECT posts.*, count(uw.user_id) as comments
                FROM posts
                    LEFT JOIN user_watchlist uw on posts.id = uw.post_id
                GROUP BY 1
                ORDER BY comments
                LIMIT $postNumber;";
        $sqlUsers = "SELECT users.*, count(uw.post_id) as watched_posts
                    FROM users
                        LEFT JOIN user_watchlist uw on users.id = uw.user_id
                    GROUP BY 1
                    ORDER BY watched_posts;";

        // All posts that have the least watches and
        // all users that have no posts

        $posts = $this->_dbHandler->query($sqlPosts, PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Post")->fetchAll();
        $users = $this->_dbHandler->query($sqlUsers, PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "User")->fetchAll();

        foreach ($posts as $post) {
            // Randomise watches
            for ($i = 0; $i < rand(0, 100); $i++) {
                $index = rand(0, count($users)-1);
                $chosenUser = $users[$index];

                $sql = "INSERT INTO user_watchlist VALUES ($chosenUser->id, $post->id)";

                $this->_dbHandler->query($sql);
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

    public function randomisePostTags()
    {
        // TODO: get posts with no tags
        $sql = "SELECT posts.*, count(pt.tag_id) as tags
                FROM posts
                    LEFT JOIN post_tags pt on posts.id = pt.post_id
                GROUP BY 1
                HAVING tags = 0;";

        $stmt = $this->_dbHandler->prepare($sql);
        $stmt->execute();

        $posts = $stmt->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Post");
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

            $this->_dbHandler->exec($sql);
        }
    }

}

