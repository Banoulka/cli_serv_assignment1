<?php


$view = new stdClass();
$view->title = "View Post - uGame";
$view->page = "create-post";
$view->user = true;
$view->owner = true;
$view->tags = ["Action", "Survival", "Adventure", "Horror"];

require_once("../views/posts/read.phtml");