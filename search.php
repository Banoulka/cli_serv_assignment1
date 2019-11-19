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

    if (isset($_GET["page"]) && $_GET["page"] <= $view->paginationView->totalPages()) {
        $view->page = $_GET["page"];
    }

    $view->paginationView = new Pagination("search.php", 10);
    $view->paginationView->setRecords($posts);
    $view->page = 1;

    // Get the posts with the records
    $view->posts = $view->paginationView->getRecords($view->page);

}




require_once "Views/search.phtml";