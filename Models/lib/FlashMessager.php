<?php

class FlashMessager
{

    public static function addMessage(string $message, string $colour, array $extra = null)
    {
        $msgClass = new stdClass();
        $msgClass->body = $message;
        $msgClass->colour = $colour;
        if ($extra) {
            $msgClass->extra = $extra;
        }

        Session::setSession("flash", serialize($msgClass));
    }

    public static function getMessage()
    {
        if (isset($_SESSION["flash"])) {
            $msgClass = unserialize($_SESSION["flash"]);
            unset($_SESSION["flash"]);
            return $msgClass;
        } else {
            return false;
        }
    }

}