<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

require_once "../Models/Post.php";

$view = new stdClass();
$view->title = "View Post - uGame";
$view->page = "create-post";
$view->owner = true;
$view->tags = ["Action", "Adventure", "SHit"];

if(isset($_GET["post_id"])) {
    $view->post = Post::find(["id" => $_GET["post_id"]]);
    require_once("../views/posts/read.phtml");
} else {
    Route::redirect("../page1.php");
}

