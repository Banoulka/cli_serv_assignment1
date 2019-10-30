<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->title = "Logout - uGame";
$view->page = "logout";

Authentication::logout();

Route::redirect("login.php");