<?php

$view = new stdClass();
$view->title = "Create New Post - uGame";
$view->page = "create-post";
$view->user = true;

require_once("../views/posts/create.phtml");