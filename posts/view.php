<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

require_once "../Models/Post.php";

$view = new stdClass();
$view->title = "View Post - uGame";
$view->page = "create-post";

if(isset($_GET["post_id"])) {
    // Find the post associated with the id in the url
    $view->post = Post::find(["id" => $_GET["post_id"]]);

    // Check if the current logged in user is the owner of the post
    if($view->post && Authentication::isLoggedOn())
        $view->owner = Authentication::User()->id == $view->post->user()->id;

    require_once "../views/posts/view.phtml";
} else {
    Route::redirect("../games.php");
}

