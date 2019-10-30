<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->page = "page1";
$view->title = "Page 1 - uGame";

require_once("Views/page1.phtml");