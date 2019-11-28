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

require_once "../Views/users/profile.phtml";