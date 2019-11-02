<?php

require_once "Models/Post.php";
require_once "Models/User.php";

session_start();
spl_autoload_register(
    function ($className) {
        include_once "Models/lib/" . $className . ".php";
    }
);

$view = new stdClass();
$view->page = "games";
$view->title = "Games - uGame";
$view->posts = Post::all();

require_once "Views/games.phtml";

