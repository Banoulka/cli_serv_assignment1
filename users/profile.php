<?php

session_start();
spl_autoload_register(
    function ($className) {
        include_once "../Models/lib/" . $className . ".php";
    }
);

// Require authentication to get to this page
require_once "../auth.php";

$view = new stdClass();
$view->title = Authentication::User()->name() . " - uGame";
$view->pageName = "profile";
$view->tab = isset($_GET["tab"]) ? $_GET["tab"] : "details";

Authentication::refresh();

if (isset($_POST["postDelete"])) {
    $post = Post::find(["id" => $_GET["post_id"]]);
    if ($post) {
        $post->destroy();
    } else {
        Route::redirect("profile.php?tab=posts");
    }
}

if (isset($_POST["postUnsubscribe"])) {
    $post = Post::find(["id" => $_GET["post_id"]]);
    Authentication::User()->unWatchPost($post->id);
}

if (isset($_POST["settingsChange"])) {
    setcookie("sidebar", $_POST["sidebar"], time()+60*60*24*30*12, "/");
    FlashMessager::addToast(
        "Settings Update",
        "Your settings have successfully been updated",
        "<i class=\"fas fa-cog mr-2\"></i>");

    Route::redirect("profile.php?tab=settings");
    return;
}

require_once "../Views/users/profile.phtml";