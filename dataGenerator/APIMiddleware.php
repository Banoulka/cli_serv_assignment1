<?php

require_once "../Models/User.php";


class APIMiddleware
{

    private static $apiKey = "f28ce0a0";

    public static function initCurl(string $uri) {
        $uri = $uri . "?key=" . self::$apiKey;

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
        $curl = self::initCurl("https://my.api.mockaroo.com/cli_serv_user_schema.json");

        // Get the output
//        $userArray = curl_exec($curl);

        // Fake the output (for now)
        $userArray = json_decode(Session::getSession("API_userData"));

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
}