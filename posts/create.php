<?php

require_once "../Models/Post.php";

session_start();
spl_autoload_register(
    function ($className) {
        include_once "Models/lib/" . $className . ".php";
    }
);

// Require authentication to get to this page
require_once "../auth.php";

$view = new stdClass();
$view->title = "Create New Post - uGame";
$view->page = "create-post";
$view->tags = Tag::all();

require_once "../views/posts/create.phtml";