<?php

class Route {

    public static function redirect($url)
    {
        header("Location: " . $url);
    }
}