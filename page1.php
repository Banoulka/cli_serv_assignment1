<?php

require_once "Models/Post.php";

session_start();
spl_autoload_register(function ($className) {
    require_once "Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->page = "page1";
$view->title = "Page 1 - uGame";
$view->posts = Post::all();

require_once("Views/page1.phtml");

