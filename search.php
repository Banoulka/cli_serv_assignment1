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
    $view->page = 1;
    $posts = Post::searchPosts($_GET);
    setcookie("searchParams", serialize($_GET));
    $view->searches = $_GET;

    // Do sorting
    if ($_GET["sort-by"] == "likes") {
        usort($posts, array("Post", "compareToLikes"));
    } else if ($_GET["sort-by"] == "watches") {
        usort($posts, array("Post", "compareToWatches"));
    } else if ($_GET["sort-by"] == "newest") {
        usort($posts, array("Post", "compareTo"));
    }

    $view->posts = $posts;
}




require_once "Views/search.phtml";