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
    $view->resultsCount = count($posts);

    // Setup the pagination
    $self = $_SERVER["REQUEST_URI"];
    $view->paginationView = new Pagination("$self&", 10);
    $view->paginationView->setRecords($posts);
    $view->page = 1;

    if (isset($_GET["page"]) && $_GET["page"] <= $view->paginationView->totalPages()) {
        $view->page = $_GET["page"];
    }

    // Get the posts with the records
    $view->posts = $view->paginationView->getRecords($view->page);

}




require_once "Views/search.phtml";