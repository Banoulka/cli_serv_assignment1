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

    }
}