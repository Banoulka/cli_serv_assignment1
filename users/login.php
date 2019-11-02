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
$view->page = "login";

if (isset($_POST["submit"])) {
    $email = htmlentities($_POST["email"]);
    $password = htmlentities($_POST["password"]);

    $view->formDataLogin = [
        "email" => $email,
        "password" => $password,
    ];

    if (Authentication::validateAndLogonUser($email, $password)) {
        // If logon redirct to page1
        Route::redirect("../games.php");
    } else {
        $view->errors = Authentication::$err;
    }
}

$page = new stdClass();
$page->signup = false;
require_once("../Views/users/register-login.phtml");