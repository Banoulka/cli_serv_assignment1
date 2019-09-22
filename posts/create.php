<?php

$view = new stdClass();
$view->title = "Create New Post - uGame";
$view->page = "create-post";
$view->user = true;
$view->tags = ["Action", "Shooter", "Fighting", "Stealth", "Survival", "Adventure", "Horror"];

require_once("../views/posts/create.phtml");