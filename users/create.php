<?php

$view = new stdClass();
$view->title = "Sign Up - uGame";
$view->page = "signup";
$view->user = false;

$page = new stdClass();
$page->signup = true;
require_once("../Views/users/register-login.phtml");