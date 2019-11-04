<?php

require_once "../Models/lib/NotifHelper.php";

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

require_once "../Models/Post.php";

$view = new stdClass();
$view->title = "View Post - uGame";
$view->page = "view-post";

if (isset($_GET["post_id"])) {
    // Find the post associated with the id in the url
    $view->post = Post::find(["id" => $_GET["post_id"]]);

    // Check if the current logged in user is the owner of the post
    if ($view->post && Authentication::isLoggedOn())
        $view->owner = Authentication::User()->id == $view->post->user()->id;

    $postID = $_GET["post_id"];
    if (isset($_POST["watchlistAdd"])) {
        Authentication::User()->addToWatchList($postID);
        Route::redirect("view.php?post_id=$postID");
    } else if (isset($_POST["watchlistRemove"])) {
        Authentication::User()->unWatchPost($postID);
        Route::redirect("view.php?post_id=$postID");
    }

    if (isset($_POST["likeAdd"])) {
        Authentication::User()->likePost($postID);
        Route::redirect("view.php?post_id=$postID");

    } else if (isset($_POST["likeRemove"])) {
        Authentication::User()->unLikePost($postID);
        Route::redirect("view.php?post_id=$postID");
    }



    require_once "../views/posts/view.phtml";
} else {
    Route::redirect("../games.php");
}

