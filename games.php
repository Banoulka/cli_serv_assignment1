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
$view->page = "games";
$view->title = "Games - uGame";
$view->userWatchlist = Authentication::isLoggedOn() ? Authentication::User()->watchlist() : [];

// Setup pagination
$view->paginationView = new Pagination("games.php", 10);
$view->paginationView->setRecords(Post::all());
$view->page = 1;

if (isset($_GET["page"]) && $_GET["page"] <= $view->paginationView->totalPages()) {
    $view->page = $_GET["page"];
}

// Get the posts with the records
$view->posts = $view->paginationView->getRecords($view->page);

$dataThing = new DataReader();
//$dataThing->randomisePostLikes();
//$dataThing->randomisePostComments();
//$dataThing->randomisePostTags();
//$dataThing->randomizePostTime();
//$dataThing->randomiseFollowers();
//$dataThing->randomisePostWatches();
//echo "finished";
//die();

require_once "Views/games.phtml";

