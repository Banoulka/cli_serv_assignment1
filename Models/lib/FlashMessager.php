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

    public static function addToast(string $title, string $body, string $icon)
    {
        $msgClass = new stdClass();
        $msgClass->body = $body;
        $msgClass->title = $title;
        $msgClass->icon = $icon;
        Session::setSession("toast", serialize($msgClass));
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

    public static function getToast()
    {
        if (isset($_SESSION["toast"])) {
            $msgClass = unserialize($_SESSION["toast"]);
            unset($_SESSION["toast"]);
            return $msgClass;
        } else {
            return false;
        }
    }

}