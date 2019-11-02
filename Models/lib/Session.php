<?php

class Session {

    public static function setSession($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function getSession($key)
    {
        return $_SESSION[$key];
    }

    public static function removeSession($key)
    {
        unset($_SESSION[$key]);
    }

    public static function isSet($key): bool
    {
        return isset($_SESSION[$key]);
    }
}