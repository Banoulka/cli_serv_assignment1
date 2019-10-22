<?php

$view = new stdClass();
$view->title = "ProfileName - uGame";
$view->page = "profile";
$view->user = true;

$page = new stdClass();
require_once("../Views/users/view.phtml");