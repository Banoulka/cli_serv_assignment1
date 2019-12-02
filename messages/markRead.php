<?php

require_once "../Models/User.php";
require_once "../Models/lib/NotifHelper.php";

session_start();
spl_autoload_register(
    function ($className) {
        include_once "../Models/lib/" . $className . ".php";
    }
);


if (isset($_POST["user_id"]) && Authentication::isLoggedOn()) {
    $user_id = $_POST["user_id"];
    Coversation::markReadMessages($user_id);
    Route::redirect("/users/profile.php?tab=messages&list=$user_id#messages");
} else {
    Route::redirect("/games.php");
}





