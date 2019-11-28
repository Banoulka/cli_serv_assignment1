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

$dataReader = new DataReader();
//$dataReader->randomisePostLikesBetterAlgorithim(10);
//die();


$view = new stdClass();
$view->page = "games";
$view->title = "Games - uGame";
$view->userWatchlist = Authentication::isLoggedOn() ? Authentication::User()->watchlist() : [];

// Setup pagination
$view->paginationView = new Pagination("games.php?", 50);
$view->paginationView->setRecords(Post::all(2500));
$view->page = 1;

if (isset($_GET["page"]) && $_GET["page"] <= $view->paginationView->totalPages()) {
    $view->page = $_GET["page"];
}

// Get the posts with the records
$view->posts = $view->paginationView->getRecords($view->page);

require_once "Views/games.phtml";

