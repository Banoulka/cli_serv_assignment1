<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

// Require authentication to get to this page
require_once "../auth.php";

$view = new stdClass();
$view->title = Authentication::User()->name() . " - uGame";
$view->pageName = "profile";
$view->tab = "details";

if (isset($_POST["submit"])) {

    $user = Authentication::User();
    $user->first_name = htmlentities($_POST["first_name"]);
    $user->last_name = htmlentities($_POST["last_name"]);
    $user->display_name = htmlentities($_POST["display_name"]);
    $oldEmail = $user->email;
    $user->email = htmlentities($_POST["email"]);
    $user->bio = htmlentities($_POST["bio"]);
    $user->display_pic = null;

    // If there is a file to upload, process it
    if (!empty($_FILES["display_pic"]["name"])) {
        $imageFileType =  strtolower(pathinfo($_FILES["display_pic"]["name"], PATHINFO_EXTENSION ));
        $targetDir = "../uploads/profile_pictures/";
        $userFileName = "user-$user->id.$imageFileType";
        $targetFile = $targetDir . $userFileName;
        //TODO: file checks

        move_uploaded_file($_FILES["display_pic"]["tmp_name"], $targetFile);
        Helpers::compressImage($targetFile);
        $user->display_pic = "/uploads/profile_pictures/" . $userFileName;
    }

    $logout = false;
    if ($oldEmail != $user->email) {
        // Logout and set old email
        $user->oldEmail = $oldEmail;
        $logout = true;
    }

    $user->save();

    // Finish the process by handling logout
    if ($logout) {
        Authentication::logout();
        Route::redirect("login.php");
    } else {
        Authentication::refresh();
        Route::redirect("profile.php");
    }
}
require_once("../Views/users/edit.phtml");

