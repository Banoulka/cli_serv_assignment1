<?php

session_start();
spl_autoload_register(
    function ($className) {
        include_once "../Models/lib/" . $className . ".php";
    }
);

// Require authentication to get to this page
require_once "../auth.php";


$view = new stdClass();
$view->title = Authentication::User()->name() . " - uGame";
$view->page = "profile";
$view->tab = isset($_GET["tab"]) ? $_GET["tab"] : "details";

Authentication::refresh();
require_once "../Views/users/profile.phtml";