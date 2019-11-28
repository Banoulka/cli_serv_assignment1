<?php

require_once "APIMiddleware.php";

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

for ($i = 0; $i < 5; $i++) {
    echo "============== FOR LOOP $i ================" . "<br/>";
    APIMiddleware::announcementsDataRequest();
}
echo "Completed!";