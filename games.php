<?php

require_once "Models/Post.php";
require_once "Models/Notification.php";
require_once "Models/User.php";

session_start();
spl_autoload_register(function ($className) {
    require_once "Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->page = "games";
$view->title = "Games - uGame";
$view->posts = Post::all();


$notifications = Authentication::User()->notifications()[0];
var_dump($notifications->isRead());

require_once("Views/games.phtml");

