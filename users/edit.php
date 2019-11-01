<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

// Require authentication to get to this page
require_once "../auth.php";

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

    if ($oldEmail != $user->email) {
        // Logout and set old email
        $user->oldEmail = $oldEmail;
        $user->save();
        Authentication::logout();
        Route::redirect("login.php");
    } else {
        $user->save();
        Authentication::refresh();
        require_once("../Views/users/profile.phtml");
    }
}
require_once("../Views/users/edit.phtml");

