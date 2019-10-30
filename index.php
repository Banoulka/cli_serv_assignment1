<?php

spl_autoload_register(function ($className) {
    require_once "Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->title = "uGame";
$view->page = "index";

require_once("Views/index.phtml");