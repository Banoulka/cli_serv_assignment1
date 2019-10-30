<?php

class Helpers {

    public static function echoActive($view, $page)
    {
        if ($view == $page ) {
            echo "active";
        } else {
            echo "";
        }
    }
}