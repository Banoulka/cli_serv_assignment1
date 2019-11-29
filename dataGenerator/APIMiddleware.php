<?php

require_once "../Models/User.php";
require_once "../Models/Post.php";


class APIMiddleware
{

    private static $apiKey = "f28ce0a0";

    public static function initCurl(string $uri) {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $uri,
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json'
            )
        ]);

        return $curl;
    }

    public static function userDataRequest() {
        $curl = self::initCurl("https://my.api.mockaroo.com/cli_serv_user_schema.json?key=f28ce0a0");

        // Get the output
        $userArray = json_decode(curl_exec($curl));

        // Fake the output (for now)
//        $userArray = json_decode(Session::getSession("API_userData"));

        foreach ($userArray as $data) {
            $user = new User();
            foreach ($data as $key => $value) {
                $user->$key = $value;
            }
//            var_dump($user);
            // Save the user
            $user->save();
        }

        curl_close($curl);
    }

    public static function postDataRequest() {
        $curl = self::initCurl("https://my.api.mockaroo.com/cli_serv_posts_schema.json?key=f28ce0a0");
        $postsArray = json_decode(curl_exec($curl));

        foreach ($postsArray as $data) {
            $post = new Post();
            foreach ($data as $key => $value) {
                $post->$key = $value;
            }
            $post->save();
        }
        curl_close($curl);
    }

    public static function announcementsDataRequest() {
        $curl = self::initCurl("https://my.api.mockaroo.com/cli_serv_post_announcements.json?key=f28ce0a0&min_id=4&max_id=81111");
        $commentArray = json_decode(curl_exec($curl));

        $now = new DateTime();
        $nowTimeStamp = $now->getTimestamp();

        foreach ($commentArray as $comment) {
            $randomTime = rand(999996666, $nowTimeStamp);
            $sql = "INSERT INTO post_announcements (post_id, body, timestamp) VALUES ($comment->post_id, \"$comment->body\", $randomTime)";
            Database::getInstance()->getdbConnection()->exec($sql);
        }

        curl_close($curl);
    }

    public static function commentsDataRequest() {
        $sqlPosts = "SELECT posts.*, COUNT(post_comments.user_id) as Comments
FROM posts LEFT JOIN post_comments ON posts.id = post_comments.post_id
GROUP BY posts.id
HAVING Comments = 0
ORDER BY Comments;";

        $curl = self::initCurl("https://my.api.mockaroo.com/cli_serv_comments.json?key=f28ce0a0");
        $comments = json_decode(curl_exec($curl));
        $posts = Database::getInstance()->getdbConnection()
            ->query($sqlPosts)->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Post");

        $now = new DateTime();
        $nowTimeStamp = $now->getTimestamp();

        while(count($comments) > 501) {
            $index = rand(0, count($posts)-1);
            $post = $posts[$index];
            array_splice($posts, $index, 1);

            // Random amount of comments
            $noComments = rand(1, 500);
            $commentsToAdd = array_splice($comments, 0, $noComments);
            $users = User::random($noComments);
            $sql = "";

            for($i = 0; $i < $noComments; $i++) {
                $comment = $commentsToAdd[$i];
                $randomTime = rand(999996666, $nowTimeStamp);
                $userToAdd = is_array($users) ? $users[$i] : $users;
                $sql .= "\n INSERT INTO post_comments (post_id, user_id, timestamp, body) VALUES ($post->id, $userToAdd->id, $randomTime, \"$comment->body\"); ";
            }

            Database::getInstance()->getdbConnection()->exec($sql);
        }

    }
}