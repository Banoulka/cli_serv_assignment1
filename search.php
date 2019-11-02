<?php

require_once "Models/Tag.php";

session_start();
spl_autoload_register(function ($className) {
    require_once "Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->page = "search";
$view->title = "Search - uGame";
$view->tags = Tag::all();

require_once "Views/search.phtml";