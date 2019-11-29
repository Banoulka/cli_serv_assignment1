<?php

require_once "Models/Post.php";
require_once "Models/User.php";
require_once "Models/lib/NotifHelper.php";

session_start();
spl_autoload_register(
    function ($className) {
        include_once "Models/lib/" . $className . ".php";
    }
);


$view = new stdClass();
$view->pageName = "trending";
$view->title = "Trending - uGame";
$view->userWatchlist = Authentication::isLoggedOn() ? Authentication::User()->watchlist() : [];

$posts = Post::trending();

// Setup pagination
$view->paginationView = new Pagination("trending.php?", 20);
$view->paginationView->setRecords($posts);
$view->page = 1;

if (isset($_GET["page"]) && $_GET["page"] <= $view->paginationView->totalPages()) {
    $view->page = $_GET["page"];
}

// Get the posts with the records
$view->posts = $view->paginationView->getRecords($view->page);

require_once "Views/trending.phtml";

