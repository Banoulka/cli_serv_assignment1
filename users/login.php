<?php

session_start();
spl_autoload_register(function ($className) {
    require_once "../Models/lib/" . $className . ".php";
});

$view = new stdClass();
$view->title = "Login - uGame";
$view->page = "login";

if (isset($_POST["submit"])) {
    $view->formDataLogin = [
        "email" => htmlentities($_POST["email"]),
        "password" => htmlentities($_POST["password"]),
    ];

    if (Authentication::validateAndLogonUser(htmlentities($_POST["email"]), htmlentities($_POST["password"]) )) {
        // If logon redirct to page1
        Route::redirect("/page1.php");
    } else {
        $view->errors = Authentication::$err;
    }
}

$page = new stdClass();
$page->signup = false;
require_once("../Views/users/register-login.phtml");