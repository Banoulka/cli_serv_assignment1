<?php

require_once "APIMiddleware.php";

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

APIMiddleware::userDataRequest();

echo "Completed!";