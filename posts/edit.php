<?php

session_start();
spl_autoload_register(
    function ($className) {
        include_once "../Models/lib/" . $className . ".php";
    }
);

require_once "../Models/Post.php";

// Require authentication to get to this page
require_once "../auth.php";

$view = new stdClass();
$view->title = "Edit Post - uGame";
$view->pageName = "edit-post";
$view->tags = Tag::all();

if (isset($_GET["post_id"])) {
    // Find the post associated with the id in the url
    $view->post = Post::find(["id" => $_GET["post_id"]]);

    // Check if the current logged in user is the owner of the post
    if ($view->post && Authentication::isLoggedOn())
        $view->owner = Authentication::User()->id == $view->post->user()->id;


    require_once "../views/posts/edit.phtml";
} else {
    Route::redirect("../games.php");
}



