<?php

require_once "Models/Tag.php";
require_once "Models/Post.php";

session_start();
spl_autoload_register(function ($className) {
    require_once "Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->page = "search";
$view->title = "Search - uGame";
$view->tags = Tag::all();

if (isset($_COOKIE["searchParams"])) {
    $view->searches = unserialize($_COOKIE["searchParams"]);
}

if (isset($_GET["submit"])) {
    // Search post
    $posts = Post::searchPosts($_GET);
    setcookie("searchParams", serialize($_GET));
    $view->searches = $_GET;
    var_dump($posts);
}




require_once "Views/search.phtml";