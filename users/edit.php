<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->title = "ProfileName - uGame";
$view->page = "profile";

if (isset($_POST["submit"])) {
    $user = Authentication::User();
    $user->first_name = htmlentities($_POST["first_name"]);
    $user->last_name = htmlentities($_POST["last_name"]);
    $user->display_name = htmlentities($_POST["display_name"]);
    $oldEmail = $user->email;
    $user->email = htmlentities($_POST["email"]);
    $user->bio = htmlentities($_POST["bio"]);
    $user->save();

    if ($oldEmail != $user->email) {
        // Logout
        Authentication::logout();
        Route::redirect("users/login.php");
    }
}

require_once("../Views/users/edit.phtml");

