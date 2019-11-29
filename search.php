<?php

require_once "Models/Tag.php";
require_once "Models/Post.php";

session_start();
spl_autoload_register(function ($className) {
    require_once "Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->pageName = "search";
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
    $view->paginationView = new Pagination("search.php?", 15);
    $view->paginationView->setRecords($posts);
    $view->page = 1;

    if (isset($_GET["page"]) && $_GET["page"] <= $view->paginationView->totalPages()) {
        $view->page = $_GET["page"];
    }

    Session::setSession("search_pagination", serialize($view->paginationView));

    // Get the posts with the records
    $view->posts = $view->paginationView->getRecords($view->page);

} else if (Session::isSet("search_pagination") && isset($_GET["page"])) {

    $view->paginationView = unserialize(Session::getSession("search_pagination"));
    if (isset($_GET["page"]) && $_GET["page"] <= $view->paginationView->totalPages()) {
        $view->page = $_GET["page"];
    }

    Session::setSession("search_pagination", serialize($view->paginationView));

    // Get the posts with the records
    $view->posts = $view->paginationView->getRecords($view->page);

}

require_once "Views/search.phtml";