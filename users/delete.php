<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

// Require authentication to get to this page
require_once "../auth.php";

if (isset($_POST["auth-token"])) {
    $user = Authentication::User();
    $authToken = $_POST["auth-token"];

    if (password_verify($user->id, $authToken)) {
        // Token is correct, delete user
        Authentication::logout();
        $user->destroy();
        Route::redirect("games.php");
    }
}

Route::redirect("profile.php");

