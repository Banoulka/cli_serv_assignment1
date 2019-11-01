<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

// Require authentication to get to this page
require_once "../auth.php";

$view = new stdClass();
$view->title = "Logout - uGame";
$view->page = "logout";

Authentication::logout();

Route::redirect("login.php");