<?php

require_once "../Models/User.php";

session_start();
spl_autoload_register(function ($className) {
    require_once __DIR__ . "/../Models/lib/" . $className . ".php";
});

if (Authentication::isLoggedOn() ) {
    Route::redirect("../games.php");
}

$view = new stdClass();
$view->title = "Sign Up - uGame";
$view->page = "signup";

if (isset($_POST["submit"])) {

    $user = User::findByEmail(htmlentities($_POST["email"]));
    $view->formData = [
        "first_name" => htmlentities($_POST["first_name"]),
        "last_name" => htmlentities($_POST["last_name"]),
        "email" => htmlentities($_POST["email"]),
        "password" => htmlentities($_POST["password"]),
        "confirm_password" => htmlentities($_POST["confirm_password"]),
    ];

    if($user) {
        $view->errors = ["This user already exists"];
    } else if (htmlentities($_POST["password"] != htmlentities($_POST["confirm_password"]))) {
        $view->errors = ["Passwords do not match"];
    } else {
        // if user does not exist
        $user = new User();
        $user->first_name = $view->formData["first_name"];
        $user->last_name = $view->formData["last_name"];
        $user->email = $view->formData["email"];
        $user->password = $view->formData["password"];
        $user->save();

        // Login user
        Authentication::validateAndLogonUser($user->email, $_POST["password"]);
        Route::redirect("/games.php");
    }
}

$page = new stdClass();
$page->signup = true;
require_once("../Views/users/register-login.phtml");