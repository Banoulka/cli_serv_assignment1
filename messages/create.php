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
    $user = User::find(["id" => $_POST["user_id"]]);
    $message = htmlentities($_POST["message"]);
    // Send message
    Authentication::User()->messageUser($user, $message);

    if (isset($_POST["list"])) {
        $user_id = $_POST["user_id"];
        Route::redirect("/users/profile.php?tab=messages&list=$user_id#messages");
    } else {
        FlashMessager::addMessage("Successfully messaged " . $user->name(), "primary", ["> $message"]);
        Route::redirect("/users/view.php?id=$user->id");
    }
} else {
    Route::redirect("/games.php");
}





