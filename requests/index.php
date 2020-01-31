<?php

require_once "./Core.php";
require_once "./Controller.php";
require_once "../vendor/autoload.php";
session_start();
spl_autoload_register(function ($className) {
    $file = "./controllers/$className.php";
    if (!file_exists($file)) {
        $file = "../Models/lib/$className.php";
        if (!file_exists($file)) {
            $file = "../Models/$className.php";
        }
    }
    require_once $file;
});

// Init the core library
$core = new Core();

