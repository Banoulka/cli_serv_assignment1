<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->title = "ProfileName - uGame";
$view->page = "viewProfile";

$page = new stdClass();
echo "View profile page";
//require_once("../Views/users/profile.phtml");