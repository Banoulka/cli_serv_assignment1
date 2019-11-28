<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

// Require authentication to get to this page
require_once "../auth.php";

$view = new stdClass();
$view->title = "Logout - uGame";
$view->pageName = "logout";
$currentURL = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];

if ($_SERVER["HTTP_REFERER"] != $currentURL) {
    $cameFrom = $_SERVER["HTTP_REFERER"];
    Session::setSession("referer", $cameFrom);
} else {
    $cameFrom = Session::getSession("referer");
}

Authentication::logout();

Route::redirect($cameFrom);