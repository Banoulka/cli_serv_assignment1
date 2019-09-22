<?php

$view = new stdClass();
$view->title = "Login - uGame";
$view->page = "login";
$view->user = false;

$page = new stdClass();
$page->signup = false;
require_once("../Views/users/register-login.phtml");