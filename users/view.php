<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

require_once "../Models/User.php";

$view = new stdClass();
$view->title = "ProfileName - uGame";
$view->page = "viewProfile";

if(isset($_GET["id"])) {
    // Find the post associated with the id in the url
    $view->user = User::find(["id" => $_GET["id"]]);

    // Return the user view
    require_once("../views/users/view.phtml");
} else {
    Route::redirect("../games.php");
}