<?php

require_once "Models/Post.php";

session_start();
spl_autoload_register(function ($className) {
    require_once "Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->page = "games";
$view->title = "Games - uGame";
$view->posts = array_reverse(Post::all());

require_once("Views/games.phtml");

