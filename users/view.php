<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

require_once "../Models/User.php";
require_once "../Models/lib/NotifHelper.php";

$view = new stdClass();
//$view->title = "ProfileName - uGame";
$view->page = "viewProfile";

if (isset($_GET["id"])) {
    // Find the user associated with the id in the url
    $view->user = User::find(["id" => $_GET["id"]]);
    if ($view->user) {
        $view->recentPosts = $view->user->recentPosts();
        $firstname = $view->user->first_name;
        $view->title = "$firstname - uGame";
    } else {
        $view->title = "Profile not found";
    }

    if (isset($_POST["userFollow"]))
    {
        // Follow the user
        Authentication::User()->followUser($view->user);
    }

    if (isset($_POST["userUnfollow"]))
    {
        // Follow the user
        Authentication::User()->unFollowUser($view->user);
    }

    // Return the user view
    require_once("../views/users/view.phtml");
} else {

    Route::redirect("../games.php");
}
