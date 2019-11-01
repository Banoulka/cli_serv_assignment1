<?php
define("HOMEDIR", __DIR__ . "/../../");

class Route {
    public static function redirect ($url)
    {
        header("Location: " . $url);
    }
}