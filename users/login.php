<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

if (Authentication::isLoggedOn() ) {
    Route::redirect("../games.php");
}

$view = new stdClass();
$view->title = "Login - uGame";
$view->pageName = "login";
$currentURL = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];

if ($_SERVER["HTTP_REFERER"] != $currentURL) {
    $cameFrom = $_SERVER["HTTP_REFERER"];
    Session::setSession("referer", $cameFrom);
} else {
    $cameFrom = Session::getSession("referer");
}

if (isset($_POST["submit"])) {
    $email = htmlentities($_POST["email"]);
    $password = htmlentities($_POST["password"]);

    $view->formDataLogin = [
        "email" => $email,
        "password" => $password,
    ];

    if (Authentication::validateAndLogonUser($email, $password)) {
        // If logon redirct to referer
        Session::removeSession("referer");
        Route::redirect($cameFrom);
    } else {
        $view->errors = Authentication::$err;
    }
}

$page = new stdClass();
$page->signup = false;
require_once("../Views/users/register-login.phtml");