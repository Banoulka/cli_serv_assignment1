<?php
require_once "Models/Tag.php";
session_start();
spl_autoload_register(function ($className) {
    require_once "Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->title = "uGame";
$view->pageName = "index";

require_once "Views/index.phtml";